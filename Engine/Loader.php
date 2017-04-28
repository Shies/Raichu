<?php
namespace Raichu\Engine;
use Raichu\Engine\Router;
/**
 * 装载器,支持自动/手动.
 * User: gukai@bilibili.com
 * Date: 17/2/8
 * Time: 下午6:57
 */
class Loader
{

    protected static $loaded = [];
    protected static $module;


    public function __construct($module = null)
    {
        spl_autoload_register([$this, 'autoload']);
        if (!$module) {
            $module = App::getInstance()->getRouter()->fetchModules();
        }
        static::$module = $module;
    }


    public static function loaded($name = null)
    {
        if (isset(static::$loaded[$name])) {
            return static::$loaded[$name];
        }

        return static::$loaded;
    }


    public static function import($fileName, $blockName, $suffix = '.php')
    {
        if (isset(static::$loaded[$blockName][$fileName])) {
            return true;
        }

        $blockName = strtolower($blockName);
        $fileName = ucfirst(trim($fileName, 'php')).$suffix;

        $files = PROVIDER_PATH.DS.$fileName;
        if (!file_exists($files)) {
            $files = MOD_PATH.DS. static::$module .DS.$blockName.DS.$fileName;
        }

        static::$loaded[$blockName][$fileName] = 1;
        include $files;
    }


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

