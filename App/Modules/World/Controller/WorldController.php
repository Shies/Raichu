<?php
namespace World\Controller;
use Raichu\Engine\AbstractController;
use Hello\Model\HelloModel;
use Hello\Controller\HelloController;
use Hello\Provider\HelloProvider;
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


    public function hello($request)
    {
        echo $this->make("hello_model")->shakehands();
        echo $this->make("hello_provider")->music();
        echo $this->make("hello_control")->index($request);
    }


    public function shakehands()
    {
        echo $this->make("world_provider")->lets();
        exit;
    }

}