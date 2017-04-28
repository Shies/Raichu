<?php
/**
 * 生产者.
 * User: gukai@bilibili.com
 * Date: 17/2/8
 * Time: 下午8:36
 */
use Raichu\Engine\AbstractModel;
use Raichu\Engine\App;

class HelloModel extends AbstractModel
{

    protected $_database = 'default';
    protected $_table = 'logger';


    public function __construct()
    {
        parent::initialize();
    }


    public function shakehands()
    {
        $ret = $this->get(['module' => 'operation'], ['desc' => 'id']);
        return json_encode($ret);
    }


    public function listen()
    {
        $model = App::getModel($this->_table);
        $ret = $model->where(['module' => 'operation'])->find_array();

        return $ret;
    }

}