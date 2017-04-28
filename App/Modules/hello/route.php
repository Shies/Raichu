<?php
/**
 * Restful
 * User: gukai@bilibili.com
 * Date: 17/3/2
 * Time: 下午12:32
 */
$router->get('/api/hello/logger', 'HelloController@logger');
$router->get('/api/hello/shakehands', 'HelloController@shakehands');
$router->get('/api/hello/index', 'HelloController@hello');
$router->get('/api/hello/listen', 'HelloController@listen');