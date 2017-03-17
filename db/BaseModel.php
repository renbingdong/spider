<?php
require_once 'util/LogUtil.php';
require_once 'util/TimeUtil.php';

class BaseModel {
    protected $mysqli;
    
    public function __construct($db_name='spider') {
        $db_config = require('config/db.php');
        $database = $db_config['databases'][$db_name];
        $this->mysqli = new mysqli($database['host'], $database['user'], $database['passwd'], $db_name, $database['port']);
        if ($this->mysqli->connect_errno) {
            LogUtil::sql_error('the db connect failure! error_info: ' . $this->mysqli->connect_error);
            exit;
        }
        LogUtil::sql_conn('Database connection is successful!');
        $this->mysqli->set_charset('utf8');
    }

    public function query($sql) {
        $start_time = TimeUtil::getMsecTime();
        LogUtil::sql($sql);
        if ($this->mysqli->ping()) {
            LogUtil::sql_conn('the database connection is ok!');
        } else {
            LogUtil::sql_conn('the database connection is not ok. error_info: ' . $this->mysqli->error);
        }
        $mysqli_result = $this->mysqli->query($sql);
        if ($mysqli_result === false) {
            LogUtil::sql_error('the db query failure! error_info: ' . $this->mysqli->error);
            return false;
        }
        $end_time = TimeUtil::getMsecTime();
        $exec_time = $end_time - $start_time;
        LogUtil::sql_time('For sql execution time: ' . $exec_time . 'ms. sql: ' . $sql);
        if ($mysqli_result === true) {
            return true;
        }
        $result = array();
        while ($row = $mysqli_result->fetch_array()) {
            $result[] = $row;
        }
        $mysqli_result->free();
        return $result;
    }

    public function close() {
        LogUtil::sql_conn('Database connection is closed!');
        $this->mysqli->close();
    }
}
