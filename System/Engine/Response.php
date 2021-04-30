<?php
namespace Raichu\Engine;
/**
 * 响应处理.
 * User: gukai@bilibili.com
 * Date: 17/2/21
 * Time: 下午17:51
 */
class Response
{

    /**
     * @var array $_ret 注册http响应数组
     */
    protected $_ret;


    /**
     * 通过设置成员变量来设置响应数据
     *
     * @param  string $name  键值
     * @param  mixed  $value 键值
     * @return void
     */
    public function __set($name, $value)
    {
        $this->_ret[$name] = $value;
    }


    /**
     * 通过成员变量来获取响应数据
     *
     * @param  string $name 键值
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_ret[$name];
    }


    /**
     * 设置HTTP响应头
     *
     * @param int $code 响应吗
     */
    public function setHeader($code)
    {
        $message = AbstractController::getResponseDescription($code);
        header("{$_SERVER['SERVER_PROTOCOL']} {$code} {$message}");
    }


    /**
     * 重定向地址
     *
     * @param string $url 地址
     */
    public function redirect($uri, $method = 'location', $http_response_code = 302)
    {
        switch ($method) {
            case 'refresh':
                header("Refresh:0;url=".$uri);
                break;
            default:
                header("Location: ".$uri, true, $http_response_code);
                break;
        }
        exit;
    }


    /**
     * 根据制定类型响应内容
     *
     * @param mixed $data     结构体
     * @param string $type     类型
     * @param int $json_option
     */
    public function ajaxReturn($data, $type = 'Json', $json_option = JSON_ERROR_NONE)
    {
        switch (strtoupper($type)) {
        case 'JSON':
            // 返回JSON数据格式到客户端 包含状态信息
            header('Content-Type:application/json; charset=utf-8');
            exit(json_encode($data, $json_option));
        case 'JSONP':
            // 返回JSON数据格式到客户端 包含状态信息
            header('Content-Type:text/javascript; charset=utf-8');
            exit(App::getInstance()->getRequest()->getRequest('callback').'('.json_encode($data, $json_option).');');
        default:
            return;
        }
    }


    /**
     * 中止计划(响应体)
     *
     * @param int $code
     * @param string $message
     */
    public function abort($code, $message = '')
    {
        $accept_code = AbstractController::getResponseDescription();
        if (!in_array($code, array_keys($accept_code))) {
            $code = 500; // Internal Server Error
        }
        if (!$message) {
            $message = $code.' '.AbstractController::getResponseDescription($code);
        }
        header($_SERVER['SERVER_PROTOCOL'].' '.$message);
        echo $message;
    }


    /**
     * 返回注册到$_ret的json数据
     *
     * @return void
     */
    public function response()
    {
        if ($this->_ret) {
            $this->ajaxReturn($this->_ret);
        }
    }


}
