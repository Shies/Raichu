<?php
/**
 * 数据库全局配置.
 * User: gukai@bilibili.com
 * Date: 17/2/22
 * Time: 下午12:59
 */
return [
    'default' => [
        'connection_string' => 'mysql:host=172.16.33.205;port=3308;dbname=bilibili_manager',
        'username' => 'test',
        'password' => 'test',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            PDO::ATTR_PERSISTENT => false,
        ),
        'id_column_overrides' => [
            'sysconfig' => 'name',
        ],
    ],
];
