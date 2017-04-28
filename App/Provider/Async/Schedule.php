<?php
namespace Raichu\Provider\Async;
/**
 * 使用yield实现异步非阻塞调度器
 * User: gukai@bilibili.com
 * Date: 17/1/4
 * Time: 下午5:35
 */
class Schedule
{
    // 单位ms
    const TICK_INTERVAL = 1;

    public $routineList;
    private $tickId = -1; // 定时器ID


    public function __construct()
    {
        $this->routineList = [];
    }


    public function start(\Generator $routine)
    {
        $task = new Task($routine);
        $this->routineList[] = $task;
        $this->startTick();
    }

    public function stop(\Generator $routine)
    {
        foreach ($this->routineList as $k => $task) {
            if ($task->getRoutine() == $routine) {
                unset($this->routineList[$k]);
            }
        }
    }

    private function startTick()
    {
        swoole_timer_tick(self::TICK_INTERVAL, function($timerId) {
            $this->tickId = $timerId;
            $this->run();
        });
    }

    private function stopTick()
    {
        if ($this->tickId >= 0) {
            swoole_timer_clear($this->tickId);
        }
    }

    private function handle(Task $task)
    {
        try {
            $retval = $task->getCurrent();
            if ($retval instanceof SysCall) {
                return $retval($task, $this);
            } else {
                # TODO
            }
        } catch(Exception $e) {
            $task->getRoutine()->throw($e);
            return false;
        }
    }


    private function run()
    {
        if (empty($this->routineList)) {
            $this->stopTick();
            return;
        }

        foreach ($this->routineList as $k => $task) {
            $task->run();
            if ($this->handle($task)) {
                continue;
            }
            if ($task->isFinished()) {
                unset($this->routineList[$k]);
            }
        }
    }

}