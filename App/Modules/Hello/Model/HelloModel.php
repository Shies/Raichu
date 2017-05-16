<?php
namespace Hello\Model;
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
        return 5201314;

        /*
        $ret = $this->get(['module' => 'operation'], ['desc' => 'id']);
        return json_encode($ret);
        */
    }


    public function listen()
    {
        return 1314520;

        /*
        $model = App::getModel($this->_table);
        $ret = $model->where(['module' => 'operation'])->find_array();

        return $ret;
        */
    }

}