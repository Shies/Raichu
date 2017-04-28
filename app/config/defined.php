<?php
/**
 * 常量定义.
 * User: gukai@bilibili.com
 * Date: 17/2/10
 * Time: 下午5:23
 */

// -----------------------------------------------------------------------------
// 路径常量定义
// -----------------------------------------------------------------------------
// WEB 所在目录
define('DOC_PATH', ROOT . '/public');
// 项目所在目录
define('APP_PATH', ROOT . '/app');
define('MOD_PATH', ROOT . '/app/modules');
define('TPL_PATH', ROOT . '/app/tpl');
define('PROVIDER_PATH', ROOT . '/app/provider');
// 外部库所在目录
define('DATA_PATH', ROOT . '/data');
define('CACHE_PATH', ROOT . '/data/cache');
define('DS', '/');
define('IN_ROOT', true);
define('StartTime', microtime(true));
define('TIMESTAMP', time());
// -----------------------------------------------------------------------------
// 项目常量定义
// -----------------------------------------------------------------------------
// 定义项目开始时间
defined('START_TIME') or define('START_TIME', microtime(true));
// 定义项目初始内存
defined('START_MEMORY') or define('START_MEMORY', memory_get_usage());
// 项目版本
define('VERSION', '1.0.0');
define('DEBUG', 1);
// -----------------------------------------------------------------------------
// 环境常量定义
// -----------------------------------------------------------------------------
// 定义是否 CLI 模式
define('IS_CLI', (PHP_SAPI === 'cli'));
// 定义是否 windows 环境
define('IS_WIN', (DIRECTORY_SEPARATOR === '\\'));
// 定义断言alert回调
//define('ASSERT_ALERT',
//    function ($file, $line, $code) {
//        echo "<hr>Assertion Failed: File '$file'<br /> Line '$line'<br /> Code '$code'<br /><hr />";
//    }
//);
