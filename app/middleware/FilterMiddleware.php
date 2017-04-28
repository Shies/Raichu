<?php

use Raichu\Engine\App;
use Raichu\Provider\Session;
use Raichu\Engine\Middleware;

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


    public function checkAdmin()
    {
        $this->isLogin();
        // throw new Exception("登录状态失效!", Ecode::Unauthorized);

        if (!UserLogged::isAdmin()) {
            throw new Exception('没有操作权限！', Ecode::Forbidden);
        }
    }

    public function isLogin()
    {
        if (!Session::has('uid')) {
            echo "<script>parent.location.href='/login';</script>";
            exit;
        }
    }
}
