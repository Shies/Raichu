<?php
namespace World\Controller;
use Raichu\Engine\AbstractController;
use Hello\Model\HelloModel;
use Hello\Controller\HelloController;
use Hello\Provider\HelloProvider;
use Raichu\Engine\Request;
use Raichu\Provider\Redis;
use World\Model\WorldModel;
use World\Provider\WorldProvider;
/**
 * 世界你好.
 * User: gukai@bilibili.com
 * Date: 17/3/2
 * Time: 下午7:53
 */
class WorldController extends AbstractController
{

    protected $singleton = [
        'world_provider' => WorldProvider::class,
        'hello_model' => HelloModel::class,
        'world_model' => WorldModel::class,
        'hello_provider' => HelloProvider::class,
        'hello_control' => HelloController::class,
    ];


    public function __construct()
    {
        parent::__construct();
    }


    public function afterExecuteRoute($dispatcher)
    {
        echo var_dump('foo') . PHP_EOL;
    }


    public function beforeExecuteRoute($dispatcher)
    {
        echo var_dump('bar') . PHP_EOL;
    }


    public function hello(Request $request)
    {
        echo $this->make("hello_model")->shakehands();
        echo $this->make("hello_provider")->music();
        echo $this->make("hello_control")->index($request);
    }


    public function world(Request $request)
    {
        echo $this->make("world_model")->getDBName();
        echo $this->make("world_model")->getTable();
    }


    public function shakehands()
    {
        echo $this->make("world_provider")->lets();
        $this->quit();
    }


    public function quit()
    {
        exit(0);
    }

}