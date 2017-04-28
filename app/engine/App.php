<?php
namespace bilibili\raichu\engine;
use bilibili\raichu\middleware\clockwork\Monitor;
use bilibili\raichu\middleware\clockwork\CacheStorage;

/**
 * 应用业务逻辑抽象实例
 * User: gukai@bilibili.com
 * Date: 17/2/11
 * Time: 下午4:57
 */
class App extends Container
{

    protected static $instance;


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


    public static function getInstance()
    {
        if (null == static::$instance) {
            static::$instance = new static;
        }

        return static::$instance;
    }


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


    public function getRequest()
    {
        return $this->make("request");
    }


    public function getResponse()
    {
        return $this->make("response");
    }


    public function getView()
    {
        return $this->make("view");
    }


    public function getRouter()
    {
        return $this->make("router", [$this]);
    }


    public function dispatcher()
    {
        return $this->make("dispatcher", [$this]);
    }


    public function autoload($modules = null)
    {
        return $this->make("loader", [$modules]);
    }


    public static function getModel($table, $database = 'default')
    {
        $instance = static::getInstance();
        return $instance->make("model", [$table, $database]);
    }


    public static function getDB($database = 'default')
    {
        return \ORM::get_db($database);
    }


    public static function middleware($cls, $middleware, $is_static = false)
    {
        $cls = ucfirst($cls);
        if (!$is_static) {
            (new $cls())->middleware($middleware);
        } else {
            $cls::middleware($middleware);
        }
    }

}
