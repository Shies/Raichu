<?php
/**
 * Restful
 * User: gukai@bilibili.com
 * Date: 17/3/2
 * Time: 下午12:32
 */
$router->prefix("/api/hello", "hello");

$router->get('/index', 'HelloController@index');
$router->get('/logger', 'HelloController@logger');
$router->get('/listen', 'HelloController@listen');
$router->get('/shakehands', 'HelloController@shakehands');