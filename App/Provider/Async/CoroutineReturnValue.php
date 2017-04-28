<?php
namespace Raichu\Provider\Async;
/**
 * 包装当前协程返回值
 * User: gukai@bilibili.com
 * Date: 17/1/9
 * Time: 下午6:31
 */
class CoroutineReturnValue
{

    // 当前协程返回值
    protected $value;


    public function __construct($value)
    {
        $this->value = $value;
    }


    public function getValue()
    {
        return $this->value;
    }
}