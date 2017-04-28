<?php
namespace Raichu\Provider\Async;
/**
 * 系统调用方便交出控制权进行通信.
 * User: gukai@bilibili.com
 * Date: 17/1/9
 * Time: 下午6:31
 */
class SysCall
{

    protected $callback;


    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }


    public function __invoke(Task $task, Schedule $schedule)
    {
        $callback = $this->callback;
        return $callback($task, $schedule);
    }


    public function __toString()
    {
        return get_class($this);
    }

}