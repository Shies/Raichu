<?php
namespace Raichu\Engine;
/**
 * 请求处理.
 * User: gukai@bilibili.com
 * Date: 17/2/22
 * Time: 下午16:57
 */
class Request
{

    /**
     * 获取请求头相关的参数
     * @var array
     */
    protected static $_headers;
    protected static $_httpuri;



    /**
     * 根据指定名称得到请求头相关参数
     *
     * @param string $name
     * @return array|string
     */
    public static function getHeader($name = '')
    {
        if (static::$_headers == null) {
            static::getallheaders();
        }

        if ($name) {
            return isset(static::$_headers[$name]) ? static::$_headers[$name] : '';
        }

        return static::$_headers;
    }


    /**
     * 获取所有headers的参数
     * @return vold
     */
    protected static function getallheaders()
    {
        if (function_exists('getallheaders')) {
            static::$_headers = getallheaders();
            return;
        }

        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if ((substr($name, 0, 5) == 'HTTP_') || ($name == 'CONTENT_TYPE') || ($name == 'CONTENT_LENGTH')) {
                $headers[str_replace([' ', 'Http'], ['-', 'HTTP'], ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        static::$_headers = $headers;
    }


    /**
     * 调度器设置指定http uri为当前uri
     * @param null $uri
     */
    public function setUri($uri)
    {
        if (null === static::$_httpuri) {
            static::$_httpuri = $uri ? $uri : $this->getUrl();
        }

        return $this;
    }


    /**
     * 获取当前访问的URL
     * @return mixed
     */
    public function getUrl()
    {
        return $_SERVER['REQUEST_URI'];
    }


    /**
     * 获取请求路径，用于路由模块
     * @return string
     */
    public function getUrlPath()
    {
        if (static::$_httpuri) {
            return static::$_httpuri;
        }

        $basepath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)).'/';
        $uri = substr($_SERVER['REQUEST_URI'], strlen($basepath));

        // Don't take query params on the path
        if (strstr($uri, '?')) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        // Remove trailing slash + enforce a slash at the start
        return ($uri = '/'.trim($uri, '/'));
    }


    /**
     * 获取请求方法
     * @return string
     */
    public function getMethod()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method == 'HEAD') {
            ob_start();
            $method = 'GET';
        } elseif ($method == 'POST') {
            $headers = static::getHeader();
            if (isset($headers['X-HTTP-Method-Override']) && in_array($headers['X-HTTP-Method-Override'], ['PUT', 'DELETE', 'PATCH'])) {
                $method = $headers['X-HTTP-Method-Override'];
            }
        }

        return $method;
    }


    /**
     * 判断当前是否AJAX请求
     * @return bool
     */
    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
    }


    /**
     * 获取GET参数
     *
     * @param string $name
     * @param string $default
     * @return string
     */
    public function get($name = '', $default = '')
    {
        if ($name) {
            return isset($_GET[$name]) ? $_GET[$name] : $default;
        } else {
            return $_GET;
        }
    }


    /**
     * 获取POST参数
     *
     * @param string $name
     * @param string $default
     * @return string
     */
    public function getPost($name = '', $default = '')
    {
        if ($name) {
            return isset($_POST[$name]) ? $_POST[$name] : $default;
        } else {
            return $_POST;
        }
    }


    /**
     * 获取REQUEST参数
     *
     * @param string $name
     * @param string $default
     * @return string
     */
    public function getRequest($name = '', $default = '')
    {
        if ($name) {
            return isset($_REQUEST[$name]) ? $_REQUEST[$name] : $default;
        } else {
            return $_REQUEST;
        }
    }


    /**
     * 获取COOKIE参数
     *
     * @param string $name
     * @return mixed
     */
    public function getCookie($name = '')
    {
        return ($name) ? $_COOKIE[$name] : $_COOKIE;
    }


    /**
     * 获取FILES参数
     *
     * @param $name
     * @param string $tp
     * @return string
     */
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


    /**
     * 获取输入流参数
     * @return string
     */
    public function getRawBody()
    {
        return file_get_contents('php://input');
    }

}
