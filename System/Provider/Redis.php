<?php
namespace Raichu\Provider;
use Raichu\Engine\App;

/**
 * Redis类
 * 封装了redis相关操作
 */
class Redis
{

    /**
     * @var \Redis $_conn redis对象
     */
    protected $_conn;


    /**
     * 构造函数，连接redis server
     *
     * @return void
     */
    public function __construct()
    {
        if (!class_exists(\Redis::class)) {
            return;
        }

        $redis = new \Redis();
        $config = $GLOBALS['app']->loadConfig('config')["redis"];
        $redis->connect($config['host'], $config['port']);
        $redis->auth($config["auth"]);

        $this->_conn = $redis;
    }

    /**
     * 执行Redis类的方法
     *
     * @param  string $method     方法名
     * @param  array  $parameters 参数
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->_conn, $method], $parameters);
    }

}
