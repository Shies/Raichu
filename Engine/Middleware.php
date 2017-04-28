<?php
namespace Raichu\Engine;
use Raichu\Engine\App;
/**
 * 中间件接口.
 * User: gukai@bilibili.com
 * Date: 17/2/16
 * Time: 下午8:57
 */
interface Middleware
{
    public static function getInstance(App $app);
}
