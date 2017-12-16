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
    protected $token;
    protected $request;
    protected $lifeTime;
    protected static $_instance;


    public static function getInstance(App $app)
    {
        if (static::$_instance == null) {
            static::$_instance = new static($app);
        }

        return static::$_instance;
    }


    private function __construct(App $app)
    {
        $this->lifeTime = 1800;
        $this->token = "XSRF-TOKEN";
        $this->request = $app->getRequest();
    }


    public function verify()
    {
        if (!$this->isExpired() && !isset($_COOKIE[$this->token])) {
            $this->setCookie(Session::get($this->token));
        }

        $token = $this->generateToken();
        if ($this->isExpired()) {
            return $this->setSession($token)->setCookie($token);
        }

        if ($this->isReading() && $this->compare()) {
            return $this->setSession($token)->setCookie($token);
        }

        return false;
    }


    protected function compare()
    {
        $token = $this->request->get($this->token) ?: $this->request->getPost($this->token);
        if (!$token && $this->request->getHeader($this->token)) {
            $token = $this->request->getHeader($this->token);
        }

        if (!is_string($token)) {
            return false;
        }

        return hash_equals(Session::get($this->token), $token);
    }


    protected function generateToken()
    {
        return md5(uniqid(rand(), true));
    }


    protected function setCookie($token)
    {
        unset($_COOKIE[$this->token]);
        setcookie($this->token, $token, time() + $this->lifeTime, '/', '', false, true);

        return $this;
    }


    protected function setSession($token)
    {
        ini_set('session.gc_maxlifetime', $this->lifeTime);
        ini_set('session.cookie_lifetime', $this->lifeTime);
        ini_set("session.cookie_httponly", 1);

        unset($_SESSION[$this->token]);
        Session::set($this->token, $token);

        return $this;
    }


    protected function isExpired()
    {
        return !Session::has($this->token) ? true : false;
    }


    protected function isReading()
    {
        $method = null;
        if (in_array($this->request->getMethod(), ['POST', 'GET', 'HEAD', 'OPTIONS'])) {
            $method = $this->request->getMethod();
        }

        return $method ?: false;
    }

}