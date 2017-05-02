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

    protected $params;
    protected $object = [];


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
     * 设置对象的参数
     * @param array $params
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }


    /**
     * 获取对象的参数
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }


    /**
     * 设置一个指定对象
     *
     * @param $name string
     * @param $value mixed
     */
    public function setObject($name, $value)
    {
        $this->app->$name = $value;
    }


    /**
     * 获取一个指定对象
     *
     * @param $name string
     * @return bool
     */
    public function getObject($name)
    {
        if (!$this->hasObject($name)) {
            return false;
        }

        return $this->app->$name;
    }


    /**
     * 判断是否存在对象
     *
     * @param $name
     * @return bool
     */
    private function hasObject($name)
    {
        return isset($this->app->$name);
    }

}