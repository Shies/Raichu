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
     * $app 初始化APP实例
     * $middleware 初始化中间件对象
     *
     * @var object
     */
    protected $app, $middleware;


    /**
     * 初始化构造函数
     * Controller constructor.
     */
    public function __construct()
    {
        $this->app = App::getInstance();
        if ($this->middleware) {
            $middleware = $this->middleware;
            if (is_array($middleware)) {
                $middleware[0] = new $middleware[0]();
            }
            call_user_func($middleware);
        }
    }

    /**
     * 初始化当前中间件对象
     * @param $middleware
     */
    public function middleware($middleware)
    {
        if (is_string($middleware) && strstr($middleware, '@')) {
            $middleware = explode('@', $middleware);
        }
        $this->middleware = $middleware;
    }

    /**
     * 绑定单利对象名
     *
     * @param $abstract
     * @param null $concrete
     */
    public function singleton($abstract, $concrete = null)
    {
        $this->app->bind($abstract, $concrete, true);
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
        return $this->app->make("response");
    }

}
