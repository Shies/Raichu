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

    protected $auto_render;
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
    public function render($name, $data = [], $display = false)
    {
        if (defined('TPL_PATH')) {
            $this->view->setPath(TPL_PATH);
        }

        $buffer = NULL;
        if ($display === false) {
            $buffer = $this->view->render($name, $data, $display);
        } else {
            $buffer = $this->auto_render ?: false;
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
     * controller => Hello,
     * action => index,
     * params => [1, 2, 3]
     * 控制器之间互相回调
     *
     * @return array
     */
    public function forward(array $url)
    {
        $controller = !empty($url[0]) ? $url[0]."Controller" : "HelloController";
        $method = !empty($url[1]) ? $url[1] : "index";
        $args = !empty($url[2]) ? $url[2] : NULL;

        // create controller instance and call the specified method
        $cont = new $controller;
        if (1 === count($args)) {
            $cont->$method($this->app->getRequest(), $args[0]);
        } elseif (2 === count($args)) {
            $cont->$method($this->app->getRequest(), $args[0], $args[1]);
        } else {
            $cont->$method($this->app->getRequest());
        }

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


}