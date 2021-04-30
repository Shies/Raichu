<?php
namespace Raichu\Engine;
/**
 * Container容器类，实现依赖注入
 * 通过容器类，使类的绑定与实例化解耦
 *
 * User: gukai@bilibili.com
 * Date: 17/2/11
 * Time: 下午4:57
 */
class Container implements \ArrayAccess
{
    /**
     * @var array $bindings 容器绑定的类名或者匿名方法
     */
    protected $bindings = [];

    /**
     * @var array $instances 容器绑定对象
     */
    protected $instances = [];

    /**
     * @var array $data 注册器
     */
    protected $data = [];

    /**
     * 将类名或者匿名函数注册绑定到容器.
     *
     * @param  string|array         $abstract 抽象名称
     * @param  \Closure|string|null $concrete 匿名函数、类名，如果为null，则同$abstract
     * @param  bool                 $shared   是否单例，默认非单例
     * @return void
     */
    public function bind($abstract, $concrete = null, $shared = false)
    {
        unset($this->instances[$abstract]);

        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    /**
     * 将类名或者匿名函数注册绑定到容器，并指定为单例模式.
     *
     * @param  string|array         $abstract 抽象名称
     * @param  \Closure|string|null $concrete 匿名函数、构造方法
     * @see    Container::bind()
     * @return void
     */
    public function singleton($abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * 根据抽象名称构造对象.
     *
     * @param  string $abstract   注册绑定的抽象名称
     * @param  array  $parameters 构造参数
     * @return mixed
     */
    public function make($abstract, array $parameters = [])
    {
        // 如果存在，则返回已经实例化的单例对象
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (!isset($this->bindings[$abstract])) {
            // 如果发现没有注册绑定到容器，尝试使用抽象名称来构造对象
            $concrete = $abstract;
        } else {
            $concrete = $this->bindings[$abstract]['concrete'];
        }

        $instance = $this->build($concrete, $parameters);
        if (!$instance) {   // 如果构建对象失败
            return null;
        }

        // 如果指定了单例模式，则将对象注册到$this->instances中
        if (isset($this->bindings[$abstract]) && $this->bindings[$abstract]['shared']) {
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }

    /**
     * 根据类名或者匿名方法构造对象.
     *
     * @param  string $concrete   匿名函数或者类名
     * @param  array  $parameters 构造参数
     * @return mixed
     */
    public function build($concrete, array $parameters = [])
    {
        if ($concrete instanceof \Closure) {
            return $concrete($this, $parameters);
        }

        try {
            $reflector = new \ReflectionClass($concrete);
            $constructor = $reflector->getConstructor();
            if (is_null($constructor)) {
                return new $concrete;
            }
            return $reflector->newInstanceArgs($parameters);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 直接注册单例.
     *
     * @param  string $abstract 抽象名称
     * @param  mixed  $instance 对象
     * @return void
     */
    public function instance($abstract, $instance)
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * 移除绑定
     *
     * @param  string $abstract 抽象名称
     * @return void
     */
    public function unbind($abstract = '')
    {
        if ($abstract) {
            unset($this->bindings[$abstract]);
        } else {
            $this->bindings = [];
        }
    }

    /**
     * 移除单例对象.
     *
     * @param  string $abstract 抽象名称
     * @return void
     */
    public function forgetInstance($abstract = '')
    {
        if ($abstract) {
            unset($this->instances[$abstract]);
        } else {
            $this->instances = [];
        }
    }

    /**
     * 清空容器.
     *
     * @return void
     */
    public function flush()
    {
        $this->bindings = $this->instances = [];
    }

    /**
     * 判断容器是否保存键名为$key的数据.
     *
     * @param  string $key 键名
     * @return bool
     */
    public function offsetExists($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * 获取键名为$key的数据.
     *
     * @param  string $key 键名
     * @return mixed
     */
    public function offsetGet($key)
    {
        if (!$key) {
            return false;
        }

        return $this->data[$key];
    }

    /**
     * 设置键名为$key的数据.
     *
     * @param  string $key   键名
     * @param  mixed  $value 键值
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * 移除键名为$key的数据.
     *
     * @param  string $key 键名
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * 为数据提供容器成员变量的访问方式.
     *
     * @param  string $key 键名
     * @return void
     */
    public function __get($key)
    {
        return $this->offsetGet($key);
    }

    /**
     * 为数据提供容器类成员变量的赋值方式.
     *
     * @param  string $key   键名
     * @param  mixed  $value 键值
     * @return void
     */
    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * 为数据提供容器类成员变量的移除方式.
     *
     * @param  string $key 键名
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    /**
     * 用判断容器类成员变量是否存在的方式检查数据.
     *
     * @param  string $key 键名
     * @return void|bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }
}
