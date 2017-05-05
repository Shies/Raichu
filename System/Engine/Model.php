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
    protected $_database = 'default';
    protected $_table = '';

    // orm instance for model
    protected $_instance;

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

    public function __get($key)
    {
        return isset($this->_instance->$key) ? $this->_instance->$key : null;
    }

    public function __set($key, $value)
    {
        $this->_instance->$key = $value;
    }

    public function __call($method, $args)
    {
        return ($this->_instance && method_exists($this->_instance, $method)) ? call_user_func_array(array($this->_instance, $method), $args) : false;
    }

    public function clean()
    {
        $this->_instance = \ORM::for_table($this->_table, $this->_database);
        return $this;
    }

    public static function logging($query, $time)
    {
        Monitor::getInstance()->dbQuery($query, $time);
    }
}
