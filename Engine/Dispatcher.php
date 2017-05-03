<?php
namespace Raichu\Engine;
use Raichu\Engine\App;
/**
 * 分发器.
 * User: Shies
 * Date: 2017/3/5
 * Time: 下午5:37
 */
class Dispatcher
{

    protected $router;
    protected $view;
    protected $app;
    protected $instantly_flush;


    /**
     * Dispatcher constructor.
     * @param \Raichu\Engine\App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->router = $app->getRouter();
        $this->view = $app->getView();
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
    public function parseUrl()
    {
        $this->router->parseUrl();
    }

    /**
     * 通过调度器执行Request/Middleware
     *
     * @param callable $request
     * @return bool
     */
    public function dispatch($request)
    {
        $this->router->run($request);
        return true;
    }


    /**
     * 设置对象的参数
     * @param array $params
     */
    public function getDI()
    {
        return $this->app;
    }


    /**
     * 通过调度器设置中间件
     *
     * @param $cls
     * @param $middleware
     */
    public function middleware($cls, $middleware)
    {
        App::middleware($cls, $middleware);
    }


    /**
     * controller => Hello,
     * action => index,
     * params => [1, 2, 3]
     * 控制器之间互相回调
     *
     * @return array
     */
    public function forward(array $url)
    {
        $args = !empty($url['params']) ? $url['params'] : NULL;
        $method = !empty($url['action']) ? $url['action'] : "index";
        $controller = !empty($url['controller']) ? $url['controller'] : "HelloController";

        // create controller instance and call the specified method
        $cont = new $controller;

        try {
            $ref = new \ReflectionMethod($controller, $method);
            if (1 == count($args)) {
                $ref->invokeArgs($cont, [$this->app->getRequest(), $args[0]]);
            } elseif (2 == count($args)) {
                $ref->invokeArgs($cont, [$this->app->getRequest(), $args[0], $args[1]]);
            } else {
                $ref->invokeArgs($cont, [$this->app->getRequest()]);
            }
        } catch(\Exception $e) {
            return false;
        }
    }


}