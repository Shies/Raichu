<?php
namespace Raichu\Engine;
use Raichu\Engine\App;
use Raichu\Middleware\Clockwork\Monitor;

/**
 * 自动识别和手动配置路由.
 * User: gukai@bilibili.com
 * Date: 17/2/19
 * Time: 下午19:12
 */
class Router
{

    protected $app;
    protected static $modules = 'hello';
    protected static $controller = 'Hello';
    protected static $method;
    protected static $params = [];
    protected static $autoAction = 'index';

    /**
     * @var static router instance, for global call
     */
    protected static $_instance;

    /**
     * @var array The route patterns and their handling functions
     */
    protected static $routes = array();

    /**
     * @var object|callable The function to be executed when no route has been matched
     */
    protected $notFound;

    /**
     * @var string The current route name
     */
    protected $routeName = '';

    /**
     * @var array The result of current route
     */
    protected $routeInfo = [];


    /**
     * 反射访问指定的函数
     *
     * @param $method
     * @param $params
     */
    public function __call($method, $params)
    {
        $accept_method = array('get', 'post', 'patch', 'delete', 'put', 'options');
        if (in_array($method, $accept_method) && count($params) >= 2) {
            $this->match(strtoupper($method), $params[0], $params[1]);
        }
    }


    /**
     * 初始化APPDI对象
     * Router constructor.
     */
    public function __construct()
    {
        $this->app = App::getInstance();
    }


    /**
     * 解析当前router地址 非restful
     */
    public function parseUrl()
    {
        if (!headers_sent()) {
            header("Access-Control-Allow-Origin", "*");
            header("Access-Control-Allow-Headers", "GET, PUT, POST, OPTIONS, DELETE, PATCH");
        }

        $segments = [];
        $uri = $this->app->make("request")->getUrlPath();
        if ($uri && $uri != '/') {
            $uri = parse_url($uri, PHP_URL_PATH);
            $uri = str_replace(['//', '../'], '/', trim($uri, '/'));
            $uri = preg_replace("|.php$|", "", $uri); // 兼容.php后缀
            if ($uri) {
                $segments = explode('/', preg_replace("|/*(.+?)/*$|", "\\1", $uri));
            }
        }

        reset($segments);
        if (current($segments)) {
            if (is_dir(ROOT .'/app/modules/'.$segments[0])) {
                static::$modules = $segments[0];
                unset($segments[0]);
                if (isset($segments[1])) {
                    static::$controller = $segments[1];
                    unset($segments[1]);
                    if (isset($segments[2])) {
                        static::$method = $segments[2];
                        unset($segments[2]);
                    }
                }
            } else {
                static::$controller = $segments[0];
                unset($segments[0]);
                if (isset($segments[1])) {
                    static::$method = $segments[1];
                    unset($segments[1]);
                }
            }

            static::$params = array_values($segments);
        }
    }


    /**
     * 默认处理当前routerURL
     *
     * @param null $request
     * @return bool
     */
    public function autoHandle($request = null)
    {
        if (!isset($this->app->config['modules'])) {
            return false;
        }

        $modules = $this->fetchModules();
        $controller = $this->fetchController();
        $method = $this->fetchMethod();
        $params = $this->fetchParams();

        // 判断是否启动了modules
        if (
            isset($this->app->config['modules']) &&
            !in_array($modules, $this->app->config['modules'])
        ) {
            return false;
        }

        if (array_key_exists($controller, spl_classes())) {
            return false;
        }

        if (class_exists($controller)) {
            $ref = new \ReflectionMethod($controller, $method);
            if ($ref->isPublic() && $ref->isStatic()) {
                if (is_callable([new $controller, 'beforeExecuteRoute'])) {
                    $controller::beforeExecuteRoute(null);
                }
                $controller::$method($params);
                if (is_callable([new $controller, 'afterExecuteRoute'])) {
                    $controller::afterExecuteRoute(null);
                }
                goto _return;
            }

            if ($ref->isPublic() && !$ref->isStatic()) {
                $ctl = new $controller();
                if (is_callable([$ctl, $method])) {
                    if (is_callable([$ctl, 'beforeExecuteRoute'])) {
                        $ctl->beforeExecuteRoute(null);
                    }
                    call_user_func_array([$ctl, $method], $params);
                    if (is_callable([$ctl, 'afterExecuteRoute'])) {
                        $ctl->afterExecuteRoute(null);
                    }
                    goto _return;
                }
            }
        }

_return:
        return (TRUE);
    }


    /**
     * 获取当前请求模块
     *
     * @param string $slash
     * @return mixed|null|string
     */
    public function fetchModules($slash = DIRECTORY_SEPARATOR)
    {
        $uri = $this->app->make("request")->getUrlPath();
        if ($slash === $uri) {
            return static::$modules;
        }

        $uri = explode($slash, trim($uri, $slash));
        reset($uri);
        $module = current($uri);

        $item = null;
        if (in_array(strtoupper($module), ['V4', 'API'])) {
            next($uri);
            $item = current($uri);
        }

        if (null === $item) {
            return static::$modules;
        }

        return (static::$modules = $item);
    }


    /**
     * 获取当前请求控制器
     * @return string
     */
    public function fetchController()
    {
        return ucfirst(static::$controller) . 'Controller';
    }


    /**
     * 获取当前请求方法
     * @return string
     */
    public function fetchMethod()
    {
        if (!static::$method) {
            return static::$autoAction;
        }

        return static::$method;
    }


    /**
     * 获取当前请求参数
     * @return array
     */
    public function fetchParams()
    {
        if (
            isset($this->app->config['is_enable_response']) &&
            false !== $this->app->config['is_enable_response']
        ) {
            array_unshift(static::$params, $this->app->make("response"));
        }

        array_unshift(static::$params, $this->app->make("request"));
        return static::$params;
    }


    /**
     * Set the 404 handling function.
     *
     * @param object|callable $fn The function to be executed
     */
    public function set404($fn)
    {
        if (is_string($fn) && strstr($fn, '@')) {
            $fn = explode('@', $fn);
        }
        $this->notFound = $fn;
    }

    public function match($methods, $pattern, $argvs)
    {
        $pattern = '/'.trim($pattern, '/');
        $fn = $argvs;
        $name = $middleware = $final = null;
        if (is_array($argvs) && isset($argvs['uses'])) {
            $fn = $argvs['uses'];
            $name = isset($argvs['as']) ? $argvs['as'] : '';
            $middleware = isset($argvs['middleware']) ? $argvs['middleware'] : '';
            $final = (isset($argvs['final']) && is_bool($argvs['final'])) ? $argvs['final'] : true;
        }
        if (is_string($fn) && strstr($fn, '@')) {
            $fn = explode('@', $fn);
        }

        foreach (explode('|', $methods) as $method) {
            $method = strtoupper($method);
            static::$routes[$method][] = [
                'as' => $name,
                'pattern' => $pattern,
                'middleware' => $middleware,
                'fn' => $fn,
                'final' => $final,
            ];
        }
    }

    public function getRouteName()
    {
        return $this->routeName;
    }

    public function getInfo()
    {
        return $this->routeInfo;
    }

    /**
     * Execute the router: Loop all defined before middlewares and routes, and execute the handling function if a match was found.
     *
     * @param object|callable $callback Function to be executed after a matching route was handled (= after router middleware)
     */
    public function run($callback = null)
    {
        $method = $this->app->make("request")->getMethod();

        $handled = false;
        if (isset(static::$routes[$method])) {
            $handled = $this->handle(static::$routes[$method]);
            if (!$handled) {
                $handled = $this->autoHandle($this->app->make("request"));
            }
        }

        if (!$handled) {
            // Handle 404
            $notFound = $this->notFound;
            if (!$notFound) {
                $this->app->make("response")->abort(404);
            }
            if (is_array($notFound)) {
                $notFound[0] = new $notFound[0]();
            }
            if (!is_callable($notFound)) {
                $this->app->make("response")->abort(404);
            }
            call_user_func($notFound);
        } else {
            // After router middleware
            if (is_string($callback) && strstr($callback, '@')) {
                $callback = explode('@', $callback);
                $callback[0] = new $callback[0]();
                call_user_func($callback);
            } elseif ($callback) {
                $callback();
            }
        }

        // If it originally was a HEAD request, clean up after ourselves by emptying the output buffer
        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_end_clean();
        }

        if ($this->app->debug) {
            Monitor::getInstance()->endEvent('App Request');
        }
    }

    /**
     * Handle a a set of routes: if a match is found, execute the relating handling function.
     *
     * @param array $routes Collection of route patterns and their handling functions
     *
     * @return int The number of routes handled
     */
    protected function handle($routes)
    {
        // The current page URL
        $currentUri = $this->app->make("request")->getUrlPath();

        // Loop all routes
        foreach ($routes as $route) {

            // we have a match!
            if (preg_match_all('#^'.$route['pattern'].'$#', $currentUri, $matches, PREG_OFFSET_CAPTURE)) {

                // Rework matches to only contain the matches, not the orig string
                $matches = array_slice($matches, 1);

                // Extract the matched URL parameters (and only the parameters)
                $params = array_map(
                    function ($match, $index) use ($matches) {
                        // We have a following parameter: take the substring from the current param position until the next one's position (thank you PREG_OFFSET_CAPTURE)
                        if (isset($matches[$index + 1]) && isset($matches[$index + 1][0]) && is_array($matches[$index + 1][0])) {
                            return trim(substr($match[0][0], 0, $matches[$index + 1][0][1] - $match[0][1]), '/');
                        } else {
                            // We have no following parameters: return the whole lot
                            return isset($match[0][0]) ? trim($match[0][0], '/') : null;
                        }

                    }, $matches, array_keys($matches)
                );
                $params = array_merge([$this->app->make("request")], $params);

                // call the handling function with the URL parameters
                if ($route['as']) {
                    $this->routeName = $route['as'];
                }
                if ($route['middleware']) {
                    $before = $route['middleware'];
                    if (is_string($before) && strstr($before, '@')) {
                        $before = explode('@', $before);
                        $before[0] = $before[0]::getInstance();
                    }
                    call_user_func_array($before, $params);
                }

                if (is_array($route['fn'])) {
                    $this->routeInfo = ['controller' => $route['fn'][0], 'method' => $route['fn'][1]];
                    $route['fn'][0] = new $route['fn'][0]();
                } else {
                    $this->routeInfo['controller'] = 'Anonymous';
                    $this->routeInfo['method'] = is_string($route['fn']) ? $route['fn'] : 'Anonymous';
                }
                call_user_func_array($route['fn'], $params);

                // check if quit after deal with the route
                if (isset($route['final']) && $route['final'] === false) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }
}
