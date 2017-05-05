<?php
namespace Raichu\Engine;
/**
 * 装载器,支持自动/手动.
 * User: gukai@bilibili.com
 * Date: 17/2/8
 * Time: 下午6:57
 */
class Loader
{
    /**
     * 返回已经加载的文件
     * @var array
     */
    protected static $loaded = [];


    /**
     * Loader constructor.
     * @param null $module
     */
    public function __construct()
    {
        \spl_autoload_register([$this, 'autoload']);
    }


    /**
     * 返回已经加载的文件
     *
     * @param null $name
     * @return array
     */
    public static function loaded($name = null)
    {
        if (isset(static::$loaded[$name])) {
            return static::$loaded[$name];
        }

        return static::$loaded;
    }


    /**
     * 载入指定模块的文件
     *
     * @param $fileName
     * @param $blockName
     * @param string $suffix
     * @return bool
     */
    public static function import($fileName, $blockName, $suffix = '.php')
    {
        if (isset(static::$loaded[$blockName][$fileName])) {
            return true;
        }

        $blockName = strtolower($blockName);
        $fileName = ucfirst(trim($fileName, 'php')).$suffix;

        $files = PROVIDER_PATH.DS.'*';
        foreach (glob($files) AS $val) {
            if (basename($val) == $fileName) {
                include_once $val;
                break;
            }
        }

        $files = MOD_PATH.DS.'*'.DS. $blockName .DS. $fileName;
        foreach (glob($files) AS $val) {
            if (basename($val) == $fileName) {
                include_once $val;
                break;
            }
        }

        static::$loaded[$blockName][$fileName] = 1;
    }


    /**
     * 载入指定文件函数
     *
     * @param $class
     * @return array|bool
     * @throws \Exception
     */
    public function autoload($class)
    {
        if (in_array($class, spl_classes())) {
            return false;
        }

        if ('Model' === substr($class, -5)) {
            $this->model($class);
        } elseif ('Provider' === substr($class, -8)) {
            $this->provider($class);
        } elseif ('Controller' === substr($class, -10)) {
            $this->controller($class);
        } else {
            throw new \Exception("The {$class} Not Found.");
        }

        return static::$loaded;
    }

    /**
     * 递归载入模型文件函数
     *
     * @param $name
     * @return bool|void
     */
    public function model($name)
    {
        if (is_array($name)) {
            foreach ($name AS $val) {
                $this->model($val);
            }
            return;
        }

        $method = __FUNCTION__;
        if ($name == '' ||
            isset(static::$loaded[$method][$name])) {
            return;
        }

        if ($method === strtolower(substr($name, -5))) {
            $name = preg_replace('/model$/i', '', $name);
        }

        return static::import($name, $method);
    }


    /**
     * 递归载入库文件函数
     *
     * @param $name
     * @return bool|void
     */
    public function provider($name)
    {
        if (is_array($name)) {
            foreach ($name AS $val) {
                $this->provider($val);
            }
            return;
        }

        $method = __FUNCTION__;
        if ($name == '' ||
            isset(static::$loaded[$method][$name])) {
            return;
        }

        if (false !== strripos(strtolower($name), 'Provider')) {
            $name = str_replace(['provider', 'Provider'], '', $name);
        }

        return static::import($name, $method);
    }

    /**
     * 递归载入控制器文件函数
     *
     * @param $name
     * @return bool|void
     */
    public function controller($name)
    {
        if (is_array($name)) {
            foreach ($name AS $val) {
                $this->controller($val);
            }
            return;
        }

        $method = __FUNCTION__;
        if ($name == '' ||
            isset(static::$loaded[$method][$name])) {
            return;
        }

        if ($method === strtolower(substr($name, -10))) {
            $name = preg_replace('/controller$/i', '', $name);
        }

        return static::import($name, $method);
    }

}

