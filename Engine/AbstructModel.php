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

    protected $_table = 'logger';
    protected $_database = 'default';

    // 表前缀, 可统一管理
    public static $_tbprefix;
    public static $_primary = 'id';



    public function initialize()
    {
        static::$_tbprefix = null;
        parent::__construct($this->getSource(), $this->getDBName());
    }



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



    public function add(array $data)
    {
        $this->clean()->create()->set($data)->save();
        return $this->id();
    }


    
    public function find($id, $is_array = true)
    {
        $item = $this->clean()->where(static::$_primary, $id)->find_one();
        if (!$item) {
            return false;
        }

        return $is_array ? $item->as_array() : $item;
    }



    public function update($id, array $data)
    {
        $item = $this->find($id, false);
        $item->set($data)->save();

        return intval(true);
    }



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
     * Get table name.
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


    /* *
     * load curdbuilder set write operation
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