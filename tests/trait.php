<?php

trait Contract {


    /**
     * jumpover team
     * @var array
     */
    private $_team = array('shies', 'higher', array());


    /**
     * With php's construct same
     * @return void
     */
    public function Promise($action)
    {
        if (method_exists($this, strval($action))) {
            echo $this->_promise($this->_team);
        } else {
            $this->_error('Hacking attempt.');
        }
    }


    /**
     * Can you join this team of jumpover
     * @return string
     */
    public function _promise()
    {
        $args = func_get_args();
        $ones = $args[0];

        static $team = array();
        foreach ($ones AS $key => $value) {
            if (is_array($ones[$key]))
                $this->_promise($value);
            else {
                $team[] = $ones[$key];
            }
        }

        return strtolower(join($team, ' | '));
    }


    /**
     * half miss of all people
     *
     * @param $msg
     * @return void
     */
    public function _error($msg)
    {
        die(preg_replace('~\\s?(.*?)\\.+~isU', '\\1', $msg));
    }

}