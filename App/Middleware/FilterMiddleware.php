<?php

use Raichu\Engine\App;
use Raichu\Engine\Middleware;
use Raichu\Provider\Session;

/**
 * 用户权限控制类
 * 在该类里定义各个权限的Filter方法
 * Class FilterMiddleware.
 */
class FilterMiddleware implements Middleware
{
    protected static $_instance;

    public static function getInstance(App $app = null)
    {
        if (static::$_instance == null) {
            static::$_instance = new static($app);
        }

        return static::$_instance;
    }


    # see https://github.com/Shies/privileges
    public function checkAdmin()
    {
        $this->isLogin();
        // throw new Exception("登录状态失效!", 403);

        if (!$this->isAdmin()) {
            // forbidden
            throw new Exception('没有操作权限！', 403);
        }
    }

    public function isLogin()
    {
        if (!Session::has('uid')) {
            echo "<script>parent.location.href='/login';</script>";
            exit;
        }
    }

    public function isAdmin()
    {
        return true;
    }
}
