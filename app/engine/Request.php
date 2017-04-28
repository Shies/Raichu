<?php
namespace bilibili\raichu\engine;
/**
 * 请求处理.
 * User: gukai@bilibili.com
 * Date: 17/2/22
 * Time: 下午16:57
 */
class Request
{

    protected static $_headers;


    public static function getHeader($name = '')
    {
        if (static::$_headers == null) {
            if (function_exists('getallheaders')) {
                static::$_headers = getallheaders();
            } else {
                $headers = array();
                foreach ($_SERVER as $name => $value) {
                    if ((substr($name, 0, 5) == 'HTTP_') || ($name == 'CONTENT_TYPE') || ($name == 'CONTENT_LENGTH')) {
                        $headers[str_replace(array(' ', 'Http'), array('-', 'HTTP'), ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                    }
                    static::$_headers = $headers;
                }
            }
        }
        if ($name) {
            return isset(static::$_headers[$name]) ? static::$_headers[$name] : '';
        } else {
            return static::$_headers;
        }
    }


    public function getUrl()
    {
        return $_SERVER['REQUEST_URI'];
    }


    public function getUrlPath()
    {
        $basepath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)).'/';
        $uri = substr($_SERVER['REQUEST_URI'], strlen($basepath));

        // Don't take query params on the path
        if (strstr($uri, '?')) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        // Remove trailing slash + enforce a slash at the start
        return ($uri = '/'.trim($uri, '/'));
    }


    public function getMethod()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method == 'HEAD') {
            ob_start();
            $method = 'GET';
        } elseif ($method == 'POST') {
            $headers = static::getHeader();
            if (isset($headers['X-HTTP-Method-Override']) && in_array($headers['X-HTTP-Method-Override'], array('PUT', 'DELETE', 'PATCH'))) {
                $method = $headers['X-HTTP-Method-Override'];
            }
        }

        return $method;
    }


    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
    }


    // GET Parameters
    public function get($name = '', $default = '')
    {
        if ($name) {
            return isset($_GET[$name]) ? $_GET[$name] : $default;
        } else {
            return $_GET;
        }
    }

    // POST Parameters
    public function getPost($name = '', $default = '')
    {
        if ($name) {
            return isset($_POST[$name]) ? $_POST[$name] : $default;
        } else {
            return $_POST;
        }
    }

    // Get Request
    public function getRequest($name = '', $default = '')
    {
        if ($name) {
            return isset($_REQUEST[$name]) ? $_REQUEST[$name] : $default;
        } else {
            return $_REQUEST;
        }
    }

    // Cookie
    public function getCookie($name = '')
    {
        return ($name) ? $_COOKIE[$name] : $_COOKIE;
    }

    public function getFile($name, $tp = '')
    {
        // tp should be `name`, `type`, `size`, `tmp_name`, `error`
        if ($name && isset($_FILES[$name])) {
            if (!$tp) {
                return $_FILES[$name];
            } else {
                return isset($_FILES[$name][$tp]) ? $_FILES[$name][$tp] : '';
            }
        }

        return strval(null);
    }

    // Raw body
    public function getRawBody()
    {
        return file_get_contents('php://input');
    }
}
