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
    protected $view;

    protected $auto_render;
    protected $instantly_flush;


    protected $object = [];
    protected $params = [];
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
        $this->object[$name] = $value;
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

        return $this->object[$name];
    }


    /**
     * 判断是否存在对象
     *
     * @param $name
     * @return bool
     */
    public function hasObject($name)
    {
        return isset($this->object[$name]);
    }

}