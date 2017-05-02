<?php
namespace Raichu\Engine;

/**
 * 逻辑控制及Model/View交互
 * User: gukai@bilibili.com
 * Date: 17/2/15
 * Time: 下午6:57
 */
class Controller
{

    /**
     * 初始化Application实例
     * @var object
     */
    protected $app;

    /**
     * 默认绑定对象
     * @var array
     */
    protected $autobind = [];

    /**
     * 默认单利对象
     * @var aray
     */
    protected $singleton = [];



    /**
     * 初始化构造函数
     * Controller constructor.
     */
    public function __construct()
    {
        $this->app = App::getInstance();
        array_map([$this->app, "bind"], $this->autobind);
        array_map([$this->app, "singleton"], $this->singleton);
    }



    /**
     * 构建单利对象
     *
     * @param $abstract
     * @param array $parameters
     * @return mixed
     */
    public function make($abstract, array $parameters = [])
    {
        return $this->app->make($abstract, $parameters);
    }

    /**
     * 初始化视图对象
     * @return mixed
     */
    public function getView()
    {
        return $this->app->make("view");
    }

    /**
     * 初始化响应对象
     * @return mixed
     */
    public function getResponse()
    {
        $response = $this->app->make("response");
        return $response;
    }

}
