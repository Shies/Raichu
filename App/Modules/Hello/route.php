<?php
/**
 * Restful
 * User: gukai@bilibili.com
 * Date: 17/3/2
 * Time: 下午12:32
 */
$router->prefix("/api/hello", "hello");

$router->get('/logger', 'HelloController@logger');
$router->get('/shakehands', 'HelloController@shakehands');
$router->get('/index', 'HelloController@index');
$router->get('/listen', 'HelloController@listen');