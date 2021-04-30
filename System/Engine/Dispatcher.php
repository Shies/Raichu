<?php
namespace Raichu\Engine;
/**
 * 分发器.
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
     * 立刻隐式刷新
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
     * @return bool|null
     */
    public function render($name, $data = [], $display = true)
    {
        if (defined('TPL_PATH')) {
            $this->view->setPath(TPL_PATH);
        }

        $buffer = NULL;
        if ($display === true) {
            $this->view->render($name, $data, $display);
        } else {
            $buffer = $this->view->render($name, $data, $display);
        }

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
     */
    public function make($abstract, array $parameters = null)
    {
        return $this->app->make($abstract, $parameters);
    }


    /**
     * 301/302重定向
     * @param string $url
     */
    public function redirect($uri, $method = 'location', $http_response_code = 301)
    {
        $this->app->getResponse()->redirect($uri, $method, $http_response_code);
    }


    /**
     * 通过调度器设置中间件
     *
     * @param $cls
     * @param $middleware
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
     * 获取URL参数
     * @param array $url
     */
    private function parseSegment(array $url)
    {
        $this->args = isset($url['params']) ? $url['params'] : [];
        $this->method = isset($url['action']) ? $url['action'] : 'index';
        $this->controller = isset($url['controller'])
            ? $url['controller']
            : get_class(AbstractController::getInstance());
    }


}