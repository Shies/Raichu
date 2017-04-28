<?php
namespace Raichu\Engine;
/**
 * 视图渲染-View.
 * User: gukai@bilibili.com
 * Date: 17/2/6
 * Time: 下午6:57
 */
interface ViewEngine
{
    public function setPath($path);
    public function render($name, $data, $display);
}

class View
{

    /**
     * @var array View data
     */
    protected $data;

    /**
     * @var string The path to the view file
     */
    protected $path;

    /**
     * @var string The addition engine
     */
    protected $engine = 'php';

    /**
     * @var object Engine instance
     */
    public $instance = null;

    /**
     * Create a new view instance.
     *
     * @param string $view
     * @param string $path
     * @param array  $data
     */
    public function __construct($path = '', $data = [], $engine = '')
    {
        $this->path = ($path) ? rtrim($path, '/') : '';
        $this->data = ($data) ? (array) $data : [];

        if ($engine && $engine !== 'php') {
            $this->instance = new $engine();
            if (!$this->instance instanceof ViewEngine) {
                throw new \Exception('Template engine class must implements ViewEngine', 500);
            }
            $this->engine = $engine;
            $this->instance->setPath($path);
        }
    }



    /**
     * When call function that not exists
     * Try to find it in template engine.
     */
    public function __call($method, $args)
    {
        if ($this->instance !== null && method_exists($this->instance, $method)) {
            return call_user_func_array([$this->instance, $method], $args);
        }
    }

    /**
     * Get the string contents of the view.
     *
     * @param string $callback
     * @param array  $data
     * @param bool   $display
     *
     * @return string
     */
    public function render($name, $data = [], $display = true)
    {
        if ($data && is_array($data)) {
            $this->data = array_merge($this->data, $data);
        }
        if ($this->engine == 'php') {
            if ($this->data) {
                extract($this->data);
            }

            $path = "{$this->path}/{$name}.php";
            ob_start();

            if (version_compare(PHP_VERSION, '5.4') >= 0 && !ini_get('short_open_tag') && function_exists('eval')) {
                echo eval('?>'.preg_replace('/;*\s*\?>/', '; ?>', str_replace('<?=', '<?php echo ', file_get_contents($path))));
            } else {
                include $path;
            }

            if ($display === false) {
                $buffer = ob_get_contents();
                @ob_end_clean();

                return $buffer;
            }
        } else {
            $this->instance->render($name, $this->data, $display);
        }
    }

    /**
     * Add a piece of data to the view.
     *
     * @param string|array $key
     * @param mixed        $value
     *
     * @return $this
     */
    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * Get the array of view data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the path to the view file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the path to the view.
     *
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
        if ($this->engine && $this->engine != 'php') {
            $this->instance->setPath($path);
        }

        return $this;
    }

    /**
     * Set the path to the view.
     *
     * @param string $path
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Set the path to the view.
     *
     * @param string $path
     */
    public function setEngine($engine = '')
    {
        if ($engine && $engine !== 'php') {
            $this->instance = new $engine();
            if (!$this->instance instanceof ViewEngine) {
                throw new \Exception('Template engine class must implements ViewEngine');
            }
            $this->engine = $engine;
        }

        return $this;
    }

    /**
     * Determine if a piece of data is bound.
     *
     * @param string $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Get a piece of bound data to the view.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->data[$key];
    }

    /**
     * Set a piece of data on the view.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function offsetSet($key, $value)
    {
        $this->with($key, $value);
    }

    /**
     * Unset a piece of data from the view.
     *
     * @param string $key
     */
    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Get a piece of data from the view.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function &__get($key)
    {
        return $this->data[$key];
    }

    /**
     * Set a piece of data on the view.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value)
    {
        $this->with($key, $value);
    }

    /**
     * Check if a piece of data is bound to the view.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Remove a piece of bound data from the view.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }
}
