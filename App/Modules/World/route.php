<?php
/**
 * Restful.
 * User: gukai@bilibili.cn
 * Date: 17/3/2
 * Time: 下午6:23
 */
$router->prefix("/api/world", "world");

$router->get('index', 'WorldController@hello');
$router->get('world', 'WorldController@world');
$router->get('shakehands', 'WorldController@shakehands');