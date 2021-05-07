<?php
namespace Raichu\Engine;
/**
 * 分发器/调度器.
 * User: gukai@bilibili.com
 * Date: 2017/3/5
 * Time: 下午5:37
 */
class Dispatcher
{

    /**
     * 获取路由对象
     * @var object
     */
    protected $router;

    /**
     * 获取视图对象
     * @var object
     */
    protected $view;

    /**
     * 即时刷新, flag=true|false
     * @var boolean
     */
    protected $instantly_flush;

    /**
     * 已启动的模块, 来源/Config/config.php
     * @var array
     */
    protected $module_enabled;

    /**
     * 根据url获取指定的控制器
     * @var string
     */
    protected $controller;

    /**
     * 根据url获取指定的方法
     * @var string
     */
    protected $method;

    /**
     * 根据url获取指定的参数
     * @var array
     */
    protected $args = [];



    /**
     * Dispatcher constructor.
     * @param \Raichu\Engine\App $app
     */
    public function __construct()
    {
        $this->app = $GLOBALS["app"];
        $this->router = $this->app->getRouter();
        $this->view = $this->app->make("view");
    }


    /**
     * 模块分发，根据请求地址前缀来分发到模块
     *
     * @param  string $prefix 请求地址前缀
     * @param  string $name   模块名
     * @return void
     */
    public function enable($prefix, $name)
    {
        $this->module_enabled[$prefix] = ucfirst($name);
    }


    /**
     * 渲染指定的模版函数
     *
     * @param $name
     * @param array $data
     * @param bool|false $display
     * @return null|string
     */
    public function render($name, $data = [], $display = true)
    {
        if (defined('TPL_PATH')) {
            $this->view->setPath(TPL_PATH);
        }

        /* ------------------------------- */
        // ob_start();
        // for ($i = 0; $i < 10; $i += 1) {
        //   ob_start();
        //   echo $i;
        //   $ret = ob_get_contents();
        //   ob_end_clean();
        //
        //   echo $ret."\n";
        //   flush();
        //   ob_flush();
        //   usleep(300000);
        // }
        // echo "Done...";
        // ob_end_flush();
        /* ------------------------------- */

        $buffer = NULL;
        if ($display === true) {
            $this->view->render($name, $data, $display);
        } else {
            $buffer = $this->view->render($name, $data, $display);
        }

        // if we need run in console, then need set it for true;
        // else if, it is for false;
        if ($this->instantly_flush) {
            flush();
            ob_flush();
        }

        return $buffer;
    }


    /**
     * 调度器解析RouterURL
     * @return void
     */
    public function parseRouterUrl(Request $request)
    {
        $this->router->parseUrl($request);
    }


    /**
     * 通过调度器执行Request/Callback
     *
     * @param callable $request
     * @return bool
     */
    public function dispatch(Request $request, $uri = null)
    {
        $req = $uri ? $request->setUri($uri) : $request;
        $this->router->run($req);
        return true;
    }


    /**
     * 构建单利或者普通对象
     *
     * @param $abstract
     * @param array $parameters
     * @return mixed
     *
     * $this->make('pipe', function() {
     *   $this->dummy[] = 'hello';
     *	 return $this;
     * })->make('pipe', function() {
     *   $this->dummy[] = 'world';
     *   return $this;
     * })->make('pipe', function() {
     *   return $this->dummy;
     * });
     */
    // Event loop and Event driver
    public function make($abstract, array $parameters = null)
    {
        // Javascript Promise
        // $this->reject();
        // $this->resolve();
        return $this->app->make($abstract, $parameters);
    }


    /**
     * 301/302重定向
     *
     * @param $uri
     * @param string $method
     * @param int $http_response_code
     * @return bool
     */
    public function redirect($uri)
    {
        // The request whether success debug for null
        if ($this->app->debug) {
            echo $this->dispatch($this->app->getRequest(), $uri);
        }

        // https and http can all
        if (strpos($uri, 'http') !== false) {
            // position path
            $this->app->getResponse()->redirect($uri, 'refresh');
        } else {
            // relative path
            $this->app->getResponse()->redirect($uri, 'location');
        }

        return true;
    }


    /**
     * 通过调度器设置中间件
     *
     * @param $name
     * @return false|null|object
     */
    public function getMiddleware($name)
    {
        if (!is_string($name)) {
            return false;
        }

        $instance = null;
        if (method_exists($name, "getInstance")) {
            $instance = $name::getInstance($this->app);
        }

        return $instance;
    }


    /**
     * controller => Hello::class,
     * action => index,
     * params => [one, two, three]
     * 控制器之间互相回调
     *
     * @return bool|void
     */
    public function forward(array $segment)
    {
        try {
            $this->parseSegment($segment);
            $ref = new \ReflectionMethod($this->controller, $this->method);
            $args = $ref->getParameters();

            $params = [];
            $params = array_merge($params, $this->args);
            foreach ($args AS $object) {
                if (isset($object->getClass()->name)) {
                    if ($object->getClass()->name == Request::class) {
                        array_unshift($params, $this->app->getRequest());
                        break;
                    }
                }
            }
            $ref->invokeArgs(new $this->controller, $params);
        } catch(\Exception $e) {
            return false;
        }
    }


    /**
     * 获取URL参数'
     *
     * @param array $uri
     * @return void
     */
    private function parseSegment(array $uri)
    {
        $this->args = isset($uri['params']) ? $uri['params'] : [];
        $this->method = isset($uri['action']) ? $uri['action'] : 'index';
        $this->controller = isset($uri['controller'])
            ? $uri['controller']
            : get_class(AbstractController::getInstance());
    }


}