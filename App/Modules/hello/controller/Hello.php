<?php
use Raichu\Engine\AbstractController;
use Raichu\Engine\App;
/**
 * 你好世界.
 * User: gukai@bilibili.com
 * Date: 17/2/7
 * Time: 下午6:19
 */
class HelloController extends AbstractController
{

    protected $middleware;


    public function __construct()
    {
        $this->middleware = function() {
            echo App::middleware("HelloProvider", null);
        };
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
        return;
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
        exit;
    }

}