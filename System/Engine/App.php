<?php
namespace Raichu\Engine;
use Raichu\Middleware\Clockwork\Monitor;
use Raichu\Middleware\Clockwork\CacheStorage;

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
        $this->singleton("request", Request::class);
        $this->singleton("response", Response::class);
        $this->singleton("view", View::class);
        $this->singleton("router", Router::class);
        $this->bind("dispatcher", Dispatcher::class);
        $this->bind("loader", Loader::class);
        $this->bind("model", Model::class);
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

        $storage = new CacheStorage("172.16.0.148", 11211);
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
     * 初始化视图实例
     * @return mixed
     */
    public function getView()
    {
        return $this->make("view");
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
     * 初始化中间件
     *
     * @param $cls
     * @param $middleware
     * @param bool|false $is_static
     */
    public static function middleware($cls, $middleware, $is_static = false)
    {
        $instance = static::getInstance();
        if (!$is_static) {
            $instance->bind($cls);
            return $instance->make($cls)->middleware($middleware);
        }

        $cls::middleware($middleware);
    }

}
