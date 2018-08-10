<?php
namespace Raichu\Engine;

use Raichu\Middleware\Clockwork\Monitor;
use Raichu\Middleware\Clockwork\CacheStorage;
use Raichu\Provider\Ecode;

/**
 * 应用业务逻辑抽象实例
 * User: gukai@bilibili.com
 * Date: 17/2/11
 * Time: 下午4:57
 */
class App extends Container
{

    /**
     * 初始化APP当前指针
     * @var object
     */
    protected static $instance;


    /**
     * 初始化APP构造函数
     * App constructor.
     */
    public function __construct()
    {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        $this->loadEngine();
    }


    /**
     * 载入引擎核心类
     */
    private function loadEngine()
    {
        $this->singleton('request', Request::class);
        $this->singleton('response', Response::class);
        $this->singleton('router', Router::class);
        $this->singleton('view', View::class);
        $this->bind('dispatcher', Dispatcher::class);
        $this->bind('loader', Loader::class);
        $this->bind('model', Model::class);
    }


    /**
     * 初始化当前APP实例
     * @return object
     */
    public static function getInstance()
    {
        if (null == static::$instance) {
            static::$instance = new static;
        }

        return static::$instance;
    }


    /**
     * 开启ClockWork Debug模式
     * return void
     */
    public function openDebug()
    {
        $this->debug = true;

        $storage = new CacheStorage("127.0.0.1", 11211);
        $clockwork = Monitor::getClockwork($storage);
        $this->getRouter()->get(
                '/__clockwork/(.*)', function ($request, $id) use ($clockwork) {
                header('Content-Type: application/json');
                echo $clockwork->getStorage()->retrieveAsJson($id);
                exit;
            }
        );
        Monitor::getInstance()->startEvent('App Request', 'Total Time Costs.');
    }


    /**
     * 初始化ORM数据库实例
     * @param array $config
     */
    public function setDB(array $config)
    {
        foreach ($config as $name => $option) {
            if ($this->debug) {
                $option['logging'] = true;
                \ORM::configure(
                    'logger', function ($query, $time) {
                    Model::logging($query, $time);
                }, $name
                );
            }
            \ORM::configure($option, null, $name);
        }
    }


    /**
     * 配置加载函数，根绝参数名读取配置文件
     *
     * @param  string $key 配置项文件名（不包含后缀）
     * @return array
     */
    public function loadConfig($key)
    {
        if (!isset($this->$key)) {
            if(is_file(ROOT.'/Config/'.$key.'.php')) {
                $this->$key = include ROOT.'/Config/'.$key.'.php';
            } else {
                throw new \Exception('config file '.$key.' not found', Ecode::Forbidden);
            }
        }

        return $this->$key;
    }


    /**
     * 模块分发，根据请求地址前缀来分发到模块
     *
     * @param  string $prefix 请求地址前缀
     * @param  string $name   模块名
     * @return void
     */
    public function dispatch($prefix, $name)
    {
        $this->_module_enabled[$prefix] = ucfirst($name);
    }


    /**
     * 设置系统错误处理
     * @param $level
     * @param $message
     * @param string $file
     * @param int $line
     * @param array $context
     * @throws \Exception
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        throw new \Exception($message, Ecode::ApiCallError);
    }


    /**
     * 设置系统异常处理
     * @param \Exception $e
     * @throws \Exception
     */
    public function handleException(\Exception $e)
    {
        throw new \Exception($e->getMessage(), $e->getCode());
    }


    /**
     * 初始化ORM数据库实例
     *
     * @param $table
     * @param string $database
     * @return mixed
     */
    public static function getModel($table, $database = 'default')
    {
        $instance = static::getInstance();
        return $instance->make("model", [$table, $database]);
    }


    /**
     * 初始化数据库实例
     *
     * @param string $database
     * @return mixed
     */
    public static function getDB($database = 'default')
    {
        return \ORM::get_db($database);
    }


    /**
     * 构建中间件
     *
     * @param $cls
     * @param $middleware
     * @param bool|false $is_static
     */
    public static function middleware($name)
    {
        $instance = static::getInstance();
        return $instance->dispatcher()->getMiddleware($name);
    }


    /**
     * 初始化请求实例
     * @return mixed
     */
    public function getRequest()
    {
        return $this->make("request");
    }


    /**
     * 初始化响应实例
     * @return mixed
     */
    public function getResponse()
    {
        return $this->make("response");
    }


    /**
     * 初始化当前路由实例
     * @return mixed
     */
    public function getRouter()
    {
        return $this->make("router");
    }


    /**
     * 初始化分发器实例
     * @return mixed
     */
    public function dispatcher()
    {
        return $this->make("dispatcher");
    }


    /**
     * 初始化装载器实例
     *
     * @param null $modules
     * @return mixed
     */
    public function autoload()
    {
        return $this->make("loader");
    }

}
