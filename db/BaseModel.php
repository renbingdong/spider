<?php
require_once 'util/LogUtil.php';

class BaseModel {
    protected $mysqli;
    
    public function __construct($db_name='spider') {
        $db_config = require('config/db.php');
        $database = $db_config['databases'][$db_name];
        $this->mysqli = new mysqli($database['host'], $database['user'], $database['passwd'], $db_name, $database['port']);
        if ($this->mysqli->connect_errno) {
            LogUtil::info('the db connect failure!');
        }
    }

    public function query($sql) {
        $mysqli_result = $this->mysqli->query($sql);
        if ($mysqli_result === false) {
            LogUtil::info('the db query failure! sql: ' . $sql);
            return false;
        }
        if ($mysqli_result === true) {
            return true;
        }
        $result = array();
        while (!($row = $mysqli_result->fetch_array())) {
            $result[] = $row;
        }
        $mysqli_result->free();
        return $result;
    }

    public function close() {
        $this->mysqli->close();
    }
}
