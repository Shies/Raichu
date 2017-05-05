<?php
namespace Raichu\Engine;
use Raichu\Middleware\Clockwork\Monitor;
/**
 * 数据库CURD-Model.
 * User: gukai@bilibili.com
 * Date: 17/2/18
 * Time: 下午14:57
 */
class Model
{

    /**
     * 数据表名
     * @var string
     */
    protected $_table = '';

    /**
     * 数据库名
     * @var string
     */
    protected $_database = 'default';

    /**
     * orm instance for model
     * @var \ORM
     */
    protected $_instance;


    /**
     * Model constructor.
     * @param string $table
     * @param string $database
     */
    public function __construct($table = '', $database = '')
    {
        if ($table) {
            $this->_table = $table;
        }
        if ($database) {
            $this->_database = $database;
        }
        if (!$this->_table) {
            $this->_table = strtolower(get_called_class());
        }
        $this->_instance = \ORM::for_table($this->_table, $this->_database);
    }


    /**
     * 反射获取$key
     *
     * @param $key
     * @return array|mixed|null
     */
    public function __get($key)
    {
        return isset($this->_instance->$key) ? $this->_instance->$key : null;
    }


    /**
     * 反射设置key和value
     *
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $this->_instance->$key = $value;
    }


    /**
     * 反射访问指定的函数
     *
     * @param $method
     * @param $args
     * @return bool|mixed
     */
    public function __call($method, $args)
    {
        return ($this->_instance && method_exists($this->_instance, $method)) ? call_user_func_array(array($this->_instance, $method), $args) : false;
    }


    /**
     * 获取一个干净的ORM对象
     * @return $this
     */
    public function clean()
    {
        $this->_instance = \ORM::for_table($this->_table, $this->_database);
        return $this;
    }


    /**
     * 为每个SQL设置ClockWork监控
     *
     * @param $query
     * @param $time
     */
    public static function logging($query, $time)
    {
        Monitor::getInstance()->dbQuery($query, $time);
    }

}
