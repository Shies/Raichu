<?php
namespace Raichu\Provider;
/**
 * 远程日志收集器.
 * User: gukai@bilibili.com
 * Date: 16/11/23
 * Time: 下午5:16.
 */
class Elk
{
    private static $socket;
    private static $server_ip;
    private static $server_port;

    public static function getInstance()
    {
        if (empty(self::$socket)) {
            self::$socket = \socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        }

        return self::$socket;
    }

    public static function configure($config)
    {
        self::$server_ip = $config['ip'];
        self::$server_port = $config['port'];
    }

    public static function debug($logs)
    {
        $logs['level'] = 'debug';
        self::send($logs);
    }

    public static function info($logs)
    {
        $logs['level'] = 'info';
        self::send($logs);
    }

    public static function warn($logs)
    {
        $logs['level'] = 'warn';
        self::send($logs);
    }

    public static function error($logs)
    {
        $logs['level'] = 'error';
        self::send($logs);
    }

    private static function send($logs)
    {
        $socket = self::getInstance();
        if ($socket) {
            $msg = json_encode($logs);
            $len = \socket_sendto($socket, $msg, strlen($msg), 0, self::$server_ip, self::$server_port);
            if (!is_int($len)) {
                Logger::getInstance()->error('ELK连接异常!');
            }
        }
    }
}
