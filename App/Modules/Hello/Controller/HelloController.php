<?php
namespace Hello\Controller;
use Hello\Model\HelloModel;
use Hello\Provider\HelloProvider;
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

    protected $singleton = [
        'hello_model' => HelloModel::class,
        'hello_provider' => HelloProvider::class,
    ];


    public function __construct()
    {
        parent::__construct();
    }


    public function beforeExecuteRoute($dispatcher)
    {
        echo App::middleware(HelloProvider::class, null);
    }


    public function afterExecuteRoute($dispatcher)
    {
        // return var_dump('bar') . PHP_EOL;
    }


    public function index($request)
    {
        $this->make("dispatcher")->forward(["action" => "shakehands"]);
    }


    public function logger()
    {
        \Raichu\Provider\Logger::getInstance();
    }


    public function shakehands($request)
    {
        echo $request->get('id') ?: 0;
        // echo $this->hello();
        echo $this->listen($request);
    }


    private function hello()
    {
        return $this->make("hello_model")->listen();
    }


    public function listen($request)
    {
        echo $request->get('id') ?: 0;
        var_dump($this->make("hello_provider")->music());
        exit;
    }

}