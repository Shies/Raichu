<?php
namespace Raichu\Provider;
use Raichu\Engine\App;

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
        ;
    }


    public function _init()
    {
        $config = App::getInstance()->loadConfig('config')["redis"];
        ini_set('session.save_handler', 'redis');
        ini_set('session.save_path', 'tcp://'.$config["host"].':'.$config["port"].'?auth='.$config["auth"]);
        ini_set('session.gc_maxlifetime', 86400);
        ini_set('session.cookie_lifetime', 86400);
        ini_set("session.cookie_httponly", 1);
        // ini_set('session.cookie_path', '/');
        // ini_set('session.cookie_domain', '.bilibili.com');
        ini_set('session.name', 'Raichu');
        if (!session_id()) {
            session_start();
        }
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
