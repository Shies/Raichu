<?php
use Raichu\Engine\App;
use Raichu\Provider\Session;
use Raichu\Engine\Middleware;
/**
 * 检测csrf中间件.
 * User: gukai@bilibili.com
 * Date: 17/2/23
 * Time: 上午11:07
 */
class CSRFMiddleware implements Middleware
{

    // use strict
    protected static $_instance;
    protected static $expireTime;
    protected static $createTime;
    protected $req;


    public static function getInstance(App $app)
    {
        if (static::$_instance == null) {
            static::$_instance = new static($app);
        }

        return static::$_instance;
    }


    public function __construct(App $app)
    {
        $this->req =& $app->getRequest();
        $this->createTime = time();
    }


    public function handle()
    {
        return $this->verify() && $this->compare();
    }


    public function compare()
    {
        $server = $this->req->getHeader('XSRF-TOEKN');
        $get = $this->req->get('XSRF-TOKEN');
        $post = $this->req->getPost('XSRF-TOKEN');

        if (!Session::_has('XSRF-TOKEN')) {
            Session::_set('XSRF-TOKEN', NULL);
        }

        return in_array(Session::_get('XSRF-TOKEN'), [$post, $get, $server]);
    }


    public function verify()
    {
        if (
            $this->isExpired() &&
            $this->isReading()
        ) {
            return $this->saveSession()->saveCookie();
        }

        return false;
    }


    public function generate_token()
    {
        return uniqid(md5(rand()), true);
    }


    public function saveCookie()
    {
        setcookie('XSRF-TOKEN', $this->generate_token(), time() + 1200, '/', '.bilibili.com', true, true);

        return $this;
    }


    public function saveSession()
    {
        ini_set('session.gc_maxlifetime', 1200);
        ini_set('session.cookie_lifetime', 1200);
        ini_set("session.cookie_httponly", 1);
        Session::_set('XSRF-TOKEN', $this->generate_token());

        return $this;
    }


    public function isExpired()
    {
        return ($this->expireTime != -1 && time() - $this->createTime > $this->expireTime);
    }


    public function isReading()
    {
        $method = null;
        if (array_key_exists($this->req->getMethod(), ['POST', 'GET', 'HEAD', 'OPTIONS'])) {
            $method = $this->req->getMethod();
        }

        return $method ?: false;
    }

}