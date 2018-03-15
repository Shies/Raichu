<?php
define('ROOT', __DIR__ . '/../../../');
require ROOT .'/vendor/autoload.php';

use Raichu\Provider\Async\Task;
use Raichu\Provider\Async\Schedule;
use Raichu\Provider\Async\SysCall;
use Raichu\Provider\Async\CoroutineReturnValue;
/**
 * 测试样板. (降低IO,提升吞吐,改善体验,性能可观)
 * User: gukai@bilibili.com
 * Date: 17/1/4
 * Time: 下午6:17
 */

$i = 5;


$s = new Schedule();
$s->start(task1());
$s->start(task2());
// $s->start(task2());
die('must return' . PHP_EOL);


function task1()
{
    global $i;

    echo "wait start" . PHP_EOL;
    while ($i-- > 0) {
        $content = (yield retval(file_get_contents('http://www.gukai.org')));
        if (null !== $content) {
            // die('content returned');
        }
        echo 'invoke-'.$i. PHP_EOL;
    }
    echo "wait end" . PHP_EOL;

    yield parallel(task2(), task3());
}


function task2()
{
    yield response();

    yield var_dump("Hello ") . PHP_EOL;
    yield var_dump("world!") . PHP_EOL;
}


function task3()
{
    echo "foo" . PHP_EOL;
    yield;
    echo "bar" . PHP_EOL;

    // yield var_dump('im shies, yeah, whether can run ok?');
}


function response()
{
    ob_end_clean();
    for ($i = 1; $i <= 10; $i += 1) {
        $idx = (yield retval($i));
        echo $idx . PHP_EOL;
        ob_flush();
        flush();
    }
    yield exception();
}


function exception()
{
    echo 'foo' . PHP_EOL;
    try {
        // yield shies();
    } catch (Exception $e) {
        echo "msg: {$e->getMessage()}";
    }
    echo 'bar' . PHP_EOL;
}


function retval($value)
{
    yield new CoroutineReturnValue($value);
}


function parallel()
{
    $args = func_get_args();
    return new SysCall(function(Task $task, Schedule $schedule) use ($args) {
        foreach ($args AS $val) {
            $schedule->start($val);
        }
        return Schedule::TICK_INTERVAL;
    });
}