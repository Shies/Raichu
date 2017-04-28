<?php
namespace bilibili\raichu\engine;

/**
 * 逻辑控制及Model/View交互
 * User: gukai@bilibili.com
 * Date: 17/2/15
 * Time: 下午6:57
 */
class Controller
{

    protected $middleware;
    protected $app;


    public function __construct()
    {
        $this->app = App::getInstance();
        if ($this->middleware) {
            $middleware = $this->middleware;
            if (is_array($middleware)) {
                $middleware[0] = $middleware[0];
                $middleware[0] = new $middleware[0]();
            }
            call_user_func($middleware);
        }
    }


    public function middleware($middleware)
    {
        if (is_string($middleware) && strstr($middleware, '@')) {
            $middleware = explode('@', $middleware);
        }
        $this->middleware = $middleware;
    }


    public function getView()
    {
        return $this->app->make("view");
    }


    public function getResponse()
    {
        return $this->app->make("response");
    }


    public function make($abstract, array $parameters = [])
    {
        return $this->app->make($abstract, $parameters);
    }

}
