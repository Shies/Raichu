<?php
namespace Raichu\Provider;
/**
 * 会话控制sess基.
 * User: gukai@bilibili.com
 * Date: 17/2/18
 * Time: 下午20:04
 */
class Session
{
    public static $_instance;

    public function __construct()
    {
        if (!session_id()) {
            session_start();
        }
    }


    public function _config()
    {
        // ini_set('session.save_handler', 'memcached');
        // ini_set('session.save_path', '172.18.21.249:36379');
        ini_set('session.gc_maxlifetime', 86400);
        ini_set('session.cookie_lifetime', 86400);
        ini_set("session.cookie_httponly", 1);
        ini_set('session.name', 'mng-bilibili');
    }


    public static function __callstatic($method, $params)
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }

        return call_user_func_array([self::$_instance, '_'.$method], $params);
    }

    public function _has($name)
    {
        return isset($_SESSION[$name]);
    }

    public function _get($name = '')
    {
        if (!$name) {
            return $_SESSION;
        }

        return isset($_SESSION[$name]) ? $_SESSION[$name] : '';
    }

    public function _set($name, $value)
    {
        $_SESSION[$name] = $value;

        return self::$_instance;
    }
}
