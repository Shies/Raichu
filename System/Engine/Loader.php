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
    public static function import($file, $path = null, $suffix = '.php')
    {
        if (isset(static::$loaded[$file])) {
            return true;
        }

        $fileName = ucfirst(trim($file, 'php')).$suffix;

        $result = [];
        static::traversing(APP_PATH.$path, $result);
        foreach ($result AS $val) {
            if (basename($val) == $fileName) {
                include_once $val;
                break;
            }
        }

        return static::$loaded[$file] = true;
    }


    /**
     * 递归扫描所有文件
     *
     * @param $path
     * @param $result
     * @return bool
     */
    protected static function traversing($path, &$result)
    {
        $curr = glob($path . '/*');
        if ($curr) {
            foreach ($curr as $file) {
                if (is_dir($file)) {
                    static::traversing($file, $result);
                } elseif (strtolower(substr($file, -4)) == '.php') {
                    array_push($result, $file);
                }
            }
        }

        return true;
    }


    /**
     * 载入指定文件函数
     *
     * @param $class
     * @return array|bool
     * @throws \Exception
     */
    protected function autoload($class)
    {
        if (in_array($class, spl_classes())) {
            return false;
        }

        if ('Model' === substr($class, -5)) {
            $this->load($class);
        } elseif ('Provider' === substr($class, -8)) {
            $this->load($class);
        } elseif ('Controller' === substr($class, -10)) {
            $this->load($class);
        } elseif ('Command' === substr($class, -7)) {
            $this->command($class);
        } elseif ("Middleware" === substr($class, -10)) {
            $this->middleware($class);
        } else {
            throw new \Exception("The {$class} Not Found.");
        }

        return static::$loaded;
    }


    /**
     * 递归载入控制器文件函数
     *
     * @param $name
     * @return bool|void
     */
    public function load($classname)
    {
        if (isset(static::$loaded[$classname])) {
            return true;
        }

        $path = null;

        // Load class in module
        $classname = trim($classname, '\\');
        $block = explode('\\', $classname);
        if (!in_array($block[0], ['Controllers', 'Models', 'Services'])) {
            $path .= '/Modules';
        }

        $block = explode('\\', $classname);
        $filename = array_pop($block);
        if ($block) {
            $path .= '/'.implode('/', $block);
        }

        return static::import($filename, $path);
    }


    /**
     * 递归载入命令行文件函数
     *
     * @param $name
     * @return bool|void
     */
    public function command($name)
    {
        if (is_array($name)) {
            foreach ($name AS $val) {
                $this->command($val);
            }
            return;
        }

        if ($name == '' ||
            isset(static::$loaded[$name])) {
            return;
        }

        if (stripos($name, __FUNCTION__) === false) {
            $name = $name.ucfirst(__FUNCTION__);
        }

        return static::import($name, '/Console');
    }


    /**
     * 递归载入中间件文件函数
     *
     * @param $name
     * @return bool|void
     */
    public function middleware($name)
    {
        if (is_array($name)) {
            foreach ($name AS $val) {
                $this->middleware($val);
            }
            return;
        }

        if ($name == '' ||
            isset(static::$loaded[$name])) {
            return;
        }

        if (stripos($name, __FUNCTION__) === false) {
            $name = $name.ucfirst(__FUNCTION__);
        }

        return static::import($name, '/Middleware');
    }

}

