<?php
namespace Raichu\Engine;
use Raichu\Engine\Model;
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
        static::$_tbprefix = null;
        parent::__construct($this->getSource(), $this->getDBName());
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
     * @return mixed
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
     * @return bool
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
        $item->set($data)->save();

        return intval(true);
    }


    /**
     * 数据删除函数
     *
     * @param $id
     * @return int
     */
    public function delete($id)
    {
        $item = $this->find($id, false);
        $item->delete();

        return intval(true);
    }


    /**
     * get table name
     *
     * @return string
     */
    public function getSource()
    {
        return $this->_table;
    }


    /**
     * Get Database name.
     *
     * @return string
     */
    public function getDBName()
    {
        return $this->_database;
    }


    /**
     * Get builder associated with table of this model.
     *
     * @param $table
     * @param string|null $tableAlias Table alias to use in query.
     *
     * @return Builder
     */
    public function getBuilder($table, array $where = null, $id = null)
    {
        if ($table) {
            $this->_table = $table;
        }

        $res = [];
        if (is_null($id)) {
            $res = (array) $this->get($where, null);
        } else {
            $res = (array) $this->find($id);
        }

        return $res;
    }


    /**
     * load setBuilder set write operation
     *
     * @param mixed INSERT, DELETE, UPDATE
     */
    public function setBuilder($table, array $data, $mode = 'INSERT|SELECT|UPDATE')
    {
        if (empty($data)) {
            return false;
        }

        if ($table) {
            $this->_table = $table;
        }

        // Request a transaction
        $transaction = App::getDB($this->getDBName());
        $transaction->beginTransaction();
        $res = null;
        try {
            if ($mode == 'INSERT') {
                $res = $this->add($data);
            } elseif ($mode == 'DELETE') {
                $res = $this->delete($data[static::$_primary]);
            } elseif ($mode == 'UPDATE') {
                $res = $this->update($data[static::$_primary], $data);
            } else {
                $transaction->rollBack();
            }
            $transaction->commit();
        } catch(\Exception $e) {
            $transaction->rollBack();
            throw new \Exception("database fail！(" . $e->getMessage() . ")");
        }

        return (int) $res;
    }

}