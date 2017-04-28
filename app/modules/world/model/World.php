<?php
use Raichu\Engine\AbstractModel;
/**
 * 世界你好.
 * User: gukai@bilibili.com
 * Date: 17/3/2
 * Time: 下午7:53
 */
class WorldModel extends AbstractModel
{

    protected $_database = 'default';
    protected $_table = 'logger';


    public function __construct()
    {
        parent::initialize();
    }


    public function getTable()
    {
        return parent::getSource();
    }


    public function getDBName()
    {
        return parent::getDBName();
    }

}
