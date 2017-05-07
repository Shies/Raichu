<?php
namespace Raichu\Provider\Async;
/**
 * 实现yield堆栈
 * User: gukai@bilibili.com
 * Date: 17/1/4
 * Time: 下午5:45
 */
class Task
{
    protected $stack;
    protected $routine;
    protected $exception;
    protected $retval;


    public function __construct(\Generator $routine)
    {
        $this->routine = $routine;
        $this->stack = new \SplStack();
    }


    /**
     * [run 协程调度]
     */
    public function run()
    {
        $routine = &$this->routine;

        try {

            if (!$routine) {
                return;
            }

            if ($this->exception) {
                $routine->throw($this->exception);
                $this->exception = null;
                return;
            }

            $value = $routine->current();

            // 嵌套的协程
            if ($value instanceof \Generator) {
                $this->stack->push($routine);
                $routine = $value;
                return;
            }

            // 协程的返回传送
            if ($value instanceof CoroutineReturnValue && $this->stack->count()) {
                $routine = $this->stack->pop();
                $routine->send($value->getValue());
                return;
            }

            // 嵌套的协程返回
            if (!$routine->valid() && !$this->stack->isEmpty()) {
                $routine = $this->stack->pop();
            }

            $this->retval = $value;
            $routine->next();

        } catch (\Exception $e) {

            if (!$this->stack->isEmpty()) {
                $routine = $this->stack->pop();
                $this->exception = $e;
            }

            return;
        }
    }


    public function getCurrent()
    {
        return $this->retval;
    }

    /**
     * [isFinished 判断该task是否完成]
     */
    public function isFinished()
    {
        return $this->stack->isEmpty() && !$this->routine->valid();
    }


    public function getRoutine()
    {
        return $this->routine;
    }

}