<?php
namespace Raichu\Engine;
/**
 * 抽象控制器基类.
 * User: gukai@bilibili.com
 * Date: 17/2/21
 * Time: 下午12:06
 */
abstract class AbstractController extends Controller
{
    /**
     * @var AbstractController
     * 初始化当前指针
     */
    private static $_instance;


    /**
     * AbstractController constructor.
     * 初始化当前指针
     */
    public function __construct()
    {
        self::$_instance =& $this;
        parent::__construct();
    }


    /**
     * Action 完成前执行
     */
    public function beforeExecuteRoute($dispatcher)
    {
        // false 时停止执行
        return strpos(__METHOD__, 'beforeExecuteRoute') !== false;
    }


    /**
     * @return AbstractController
     * 获取当前指针
     */
    public static function &getInstance()
    {
        return self::$_instance;
    }


    /**
     * 初始化方法
     */
    public function initialize()
    {
        return uniqid(md5(rand()), true);
    }


    /**
     * Action 完成后执行
     */
    public function afterExecuteRoute($dispatcher)
    {
        // false 时停止执行
        return strpos(__METHOD__, 'afterExecuteRoute') !== false;
    }


    /**
     * HTTP Response Code
     *
     * @param $code
     * @return string
     */
    public static function getResponseDescription($code)
    {
        $codes = array(
            // Informational 1xx
            100 => 'Continue',
            101 => 'Switching Protocols',
            // Success 2xx
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            // Redirection 3xx
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',  // 1.1
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            // 306 is deprecated but reserved
            307 => 'Temporary Redirect',
            // Client Error 4xx
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            // Server Error 5xx
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded'
        );

        $result = (isset($codes[$code])) ?
            $codes[$code] :
            'Unknown Status Code';

        return $result;
    }

}
