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
    protected $request;
    protected $provider;

    protected $view;
    protected $auto_render;
    protected $instantly_flush;

    // four segment
    protected $route = [];


    /**
     * Dispatcher constructor.
     * @param \Raichu\Engine\App $app
     */
    public function __construct(App $app)
    {
        $this->router = $app->getRouter();
        $this->request = $app->getRequest();
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
     * 通过调度器执行Request
     *
     * @param callable $request
     * @return bool
     */
    public function dispatch(callable $request)
    {
        $this->router->run($request);
        return true;
    }


    public function getNameSpace($var)
    {
        $namespace = null;
        if (!is_object($this->router)) {
            return $this;
        }

        if (method_exists($this->router, 'nameSpace')) {
            $namespace = $this->router->nameSpace($var);
        }

        return $this->route['namespace'] = $namespace;
    }


    public function getModules()
    {
        $this->route['modules'] = $this->router->fetchModules();
        return $this;
    }


    private function getController()
    {
        $this->route['controller'] = $this->router->fetchController();
        return $this;
    }


    private function getMethod()
    {
        $this->route['method'] = $this->router->fetchMethod();
        return $this;
    }


    public function getRoute()
    {
        return $this->route;
    }

}