<?php
use Raichu\Engine\AbstractController;
use Raichu\Engine\Transport;
/**
 * 你好世界.
 * User: gukai@bilibili.com
 * Date: 17/2/7
 * Time: 下午6:19
 */
class HelloController extends AbstractController
{

    public function __construct()
    {
        parent::__construct();
    }


    /*
    public function beforeExecuteRoute($dispatcher = null)
    {
        return var_dump('foo') . PHP_EOL;
    }


    public function afterExecuteRoute($dispatcher = null)
    {
        return var_dump('bar') . PHP_EOL;
    }
    */


    public function index($request)
    {
        var_dump($this->getResponse()->ajaxReturn(['ok' => false]));
        echo $request->get('id') ?: 0;
        var_dump($this->hello()) . PHP_EOL;
    }


    public function logger()
    {
        \Raichu\Provider\Logger::getInstance();
    }


    public function shakehands($request)
    {
        echo $request->get('id') ?: 0;
        echo $this->hello(),$this->listen($request);
    }


    private function hello()
    {
        return ((new HelloModel())->listen());
    }


    public function listen($request)
    {
        echo $request->get('id') ?: 0;
        echo (new HelloProvider())->music();
    }

}