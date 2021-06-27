<?php
namespace Raichu\Engine;
use Raichu\Middleware\Clockwork\Monitor;
/**
 * 自动识别和手动配置路由.
 * User: gukai@bilibili.com
 * Date: 17/2/19
 * Time: 下午19:12
 */
class Router
{

    /**
     * 初始化APP对象
     * @var object
     */
    protected $app;

    /**
     * 模块名=>路由前缀
     * @var string
     */
    protected $prefix;

    /**
     * 获取分发指定模块
     * @var string
     */
    protected static $module = 'Hello';

    /**
     * 后去分发指定控制器
     * @var string
     */
    protected static $controller = 'Hello';

    /**
     * 获取分发指定方法
     * @var string
     */
    protected static $method = 'index';

    /**
     * 获取分发指定的参数
     * @var array
     */
    protected static $params = [];

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
        $this->app = $GLOBALS["app"];
    }


    /**
     * 设置一个全局的前缀
     *
     * @param string $prefix URL前缀
     * @param string $module 设置模块名
     */
    public function prefix($prefix, $module = '')
    {
        $this->prefix = $prefix;
        if ($module) {
            static::$module = ucfirst($module);
        }
    }


    /**
     * 解析当前router地址, 默认访问模式
     * @return void
     */
    public function parseUrl(Request $request)
    {
        if (!headers_sent()) {
            // header("Access-Control-Allow-Origin: *");
            // header("Access-Control-Allow-Headers: GET, PUT, POST, OPTIONS, DELETE, PATCH");
        }

        $segments = [];
        $uri = $request->getUrlPath();
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
            if (is_dir(APP_PATH .'/Modules/'.ucfirst($segments[0]))) {
                static::$module = ucfirst($segments[0]);
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
    public function autoHandle()
    {
        if (!isset($this->app->config['modules'])) {
            return false;
        }

        $module = strtolower($this->fetchModule());
        $controller = $this->fetchController();
        $method = $this->fetchMethod();
        $params = $this->fetchParams();

        // 判断是否启动了modules
        if (
            isset($this->app->config['modules']) &&
            !array_key_exists($module, $this->app->config['modules'])
        ) {
            return false;
        }

        if (array_key_exists($controller, spl_classes())) {
            return false;
        }

        if (class_exists($controller)) {
            $ref = new \ReflectionMethod($controller, $method);
            if ($ref->isPublic() && $ref->isStatic()) {
                $ctl = new $controller();
                if (method_exists($ctl, 'beforeExecuteRoute')) {
                    $ctl->beforeExecuteRoute($this->app->dispatcher());
                }
                $controller::$method($params[0]);
                if (method_exists($ctl, 'afterExecuteRoute')) {
                    $ctl->afterExecuteRoute($this->app->dispatcher());
                }
                return true;
            }

            if ($ref->isPublic() && !$ref->isStatic()) {
                $ctl = new $controller();
                if (is_callable([$ctl, $method])) {
                    if (method_exists($ctl, 'beforeExecuteRoute')) {
                        $ctl->beforeExecuteRoute($this->app->dispatcher());
                    }
                    call_user_func_array([$ctl, $method], $params);
                    if (method_exists($ctl, 'afterExecuteRoute')) {
                        $ctl->afterExecuteRoute($this->app->dispatcher());
                    }
                    return true;
                }
            }
        }

        return (0);
    }


    /**
     * 获取当前请求模块
     *
     * @param string $slash
     * @return mixed|null|string
     */
    public function fetchModule()
    {
        return static::$module;
    }


    /**
     * 获取当前请求控制器
     * @return string
     */
    public function fetchController()
    {
        $controller = ucfirst(static::$controller) . 'Controller';
        return static::$module.'\\Controller\\'.$controller;
    }


    /**
     * 获取当前请求方法
     * @return string
     */
    public function fetchMethod()
    {
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
            $this->app->config['is_enable_response']
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


    /**
     * 匹配路由和制定路由参数
     *
     * @param $methods
     * @param $pattern
     * @param $argvs
     */
    public function match($methods, $pattern, $fn)
    {
        $pattern = '/'.trim($pattern, '/');
        if (is_string($fn) && strstr($fn, '@')) {
            $fn = explode('@', $fn);
        }

        $pattern = $this->prefix.$pattern;
        foreach (explode('|', $methods) as $method) {
            $method = strtoupper($method);
            static::$routes[$method][] = ['pattern' => $pattern, 'fn' => $fn];
        }

        return true;
    }


    /**
     * Execute the router: Loop all defined before middlewares and routes, and execute the handling function if a match was found.
     *
     * @param object|callable $callback Function to be executed after a matching route was handled (= after router middleware)
     */
    public function run(Request $request)
    {
        $method = $request->getMethod();

        $handled = false;
        if (isset(static::$routes[$method])) {
            $handled = $this->handle(static::$routes[$method], $request);
        }

        if (!$handled) {
            $this->parseUrl($request);
            $handled = $this->autoHandle();
        }

        if (!$handled) {
            // Handle 404
            $notFound = $this->notFound;
            if (!$notFound) {
                $this->app->make("response")->abort(404);
                exit;
            }
            if (is_array($notFound)) {
                $notFound[0] = new $notFound[0]();
            }
            if (!is_callable($notFound)) {
                $this->app->make("response")->abort(404);
                exit;
            }
            call_user_func($notFound);
        }

        // If it originally was a HEAD request, clean up after ourselves by emptying the output buffer
        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_end_clean();
        }

        if ($this->app->debug) {
            Monitor::getInstance()->endEvent('run Request');
        }
    }

    /**
     * Handle a a set of routes: if a match is found, execute the relating handling function.
     *
     * @param array $routes Collection of route patterns and their handling functions
     *
     * @return bool The number of routes handled
     */
    protected function handle(array $routes, Request $request)
    {
        // The current page URL
        $currentUri = $request->getUrlPath();

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
                $params = array_merge([$request], $params);

                if (is_array($route['fn'])) {
                    list($controller, $method) = $route['fn'];
                    $namespace = static::$module.'\\Controller\\'.$controller;
                    $instance = new $namespace();

                    if (method_exists($instance, 'beforeExecuteRoute')) {
                        $instance->beforeExecuteRoute($this->app->dispatcher());
                    }

                    call_user_func_array([$instance, $method], $params);
                    if (method_exists($instance, 'afterExecuteRoute')) {
                        $instance->afterExecuteRoute($this->app->dispatcher());
                    }
                } else {
                    call_user_func_array($route['fn'], $params);
                }

                return true;
            }
        }

        return false;
    }
}
