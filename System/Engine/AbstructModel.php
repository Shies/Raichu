<?php
namespace Raichu\Engine;
/**
 * 抽象模型基类.
 * User: gukai@bilibili.com
 * Date: 17/2/13
 * Time: 下午3:12
 */
abstract class AbstractModel extends Model
{
    /**
     * 数据表名
     * @var string
     */
    protected $_table = 'logger';

    /**
     * 数据库名
     * @var string
     */
    protected $_database = 'default';

    /**
     * 备份当前操作数据表
     * @var string
     */
    protected $_backup;

    /**
     * 表前缀, 可统一管理
     * @var string
     */
    public static $_tbprefix;

    /**
     * 数据库主键名
     * @var string
     */
    public static $_primary = 'id';



    /**
     * 初始化方法
     * @return void
     */
    public function initialize()
    {
        try {
            parent::__construct($this->getSource(), $this->getDataBase());
        } catch (\Exception $ex) {
            throw $ex;
        }
        $this->_backup = static::$_tbprefix . $this->_table;
    }


    /**
     * 数据获取函数(返回二维数据)
     *
     * @param array $where
     * @param ...$args
     * @return mixed
     */
    public function get(array $where, ...$args)
    {
        $model = $this->clean();
        if (is_array($where)) {
            $model->where($where);
        }

        // ['asc' => 'rank', 'desc' => 'time'], 排序map字段
        $args = array_filter($args);
        if (is_array($args)) {
            foreach ($args AS $val) {
                $model = (key($val) === 'desc' ? $model->order_by_desc($val['desc']) : $model->order_by_asc($val['asc']));
            }
        }

        return $model->find_array();
    }


    /**
     * 数据插入函数(key/value)
     *
     * @param array $data
     * @return int
     */
    public function add(array $data)
    {
        $this->clean()->create()->set($data)->save();
        return $this->id();
    }


    /**
     * 数据查找函数(返回一维数据)
     *
     * @param $id
     * @param bool|true $is_array
     * @return array|object
     */
    public function find($id, $is_array = true)
    {
        $item = $this->clean()->where(static::$_primary, $id)->find_one();
        if (!$item) {
            return false;
        }

        return $is_array ? $item->as_array() : $item;
    }


    /**
     * 数据更新函数
     *
     * @param $id
     * @param array $data
     * @return int
     */
    public function update($id, array $data)
    {
        $item = $this->find($id, false);
        foreach ($data AS $key => $val) {
            $item->$key = $val;
        }

        return $item->save();
    }


    /**
     * 数据删除函数
     *
     * @param $id
     * @return int
     */
    public function delete($id)
    {
        $item = $this->clean()->where(static::$_primary, $id)->find_one();
        if (!$item) {
            return false;
        }

        return $item->delete();
    }


    /**
     * get Source name
     *
     * @return string
     */
    public function getSource()
    {
        return $this->_table;
    }


    /**
     * get Database name.
     *
     * @return string
     */
    public function getDataBase()
    {
        return $this->_database;
    }


    /**
     * Get builder associated with table of this model.
     *
     * @param $table
     * @param string|null $tableAlias Table alias to use in query.
     *
     * @return mixed|array
     */
    public function getBuilder($table, array $where = null, $id = null)
    {
        if ($table) {
            $this->_table = $table;
        }

        $res = null;
        if (is_null($id)) {
            $res = $this->get($where, null);
        } else {
            $res = $this->find($id);
        }

        $this->_table = $this->_backup;
        return $res;
    }


    /* *
     * Load curdbuilder set write operation
     *
     * @param string $mode="INSERT|DELETE|UPDATE"
     * @return mixed|array
     */
    public function setBuilder($table, array $data, $mode = 'INSERT|DELETE|UPDATE')
    {
        if (empty($data)) {
            return false;
        }

        if ($table) {
            $this->_table = $table;
        }

        // Request a transaction
        $transaction = App::getDB($this->getDataBase());
        $transaction->beginTransaction();
        try {
            $res = null;
            if ($mode == 'INSERT') {
                $res = $this->add($data);
            } elseif ($mode == 'DELETE') {
                $res = $this->delete($data[static::$_primary]);
            } elseif ($mode == 'UPDATE') {
                $res = $this->update($data[static::$_primary], $data);
            } else {
                $transaction->rollBack();
            }
            $this->_table = $this->_backup;
            $transaction->commit();
        } catch(\Exception $e) {
            $transaction->rollBack();
            throw new \Exception("database fail！(" . $e->getMessage() . ")");
        }

        return $res;
    }

}