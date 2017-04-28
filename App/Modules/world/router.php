<?php
/**
 * Restful.
 * User: bilibili
 * Date: 17/3/2
 * Time: 下午6:23
 */
$router->get('/api/world/index', 'WorldController@hello');
$router->get('/api/world/shakehands', 'WorldController@shakehands');