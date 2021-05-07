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
     * 初始化App实例
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
     * @var array
     */
    protected $singleton = [];



    /**
     * 初始化构造函数
     * Controller constructor.
     */
    public function __construct()
    {
        $this->app = $GLOBALS["app"];
        if ($this->autobind) {
            foreach ($this->autobind AS $name => $class) {
                $this->app->bind($name, $class);
            }
        }

        if ($this->singleton) {
            foreach ($this->singleton AS $name => $class) {
                $this->app->singleton($name, $class);
            }

            null;
        }
    }


    /**
     * 构建单利或者普通对象
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
     * 初始化响应对象
     * @return mixed
     */
    public function getResponse()
    {
        return $this->make("response");
    }

}
