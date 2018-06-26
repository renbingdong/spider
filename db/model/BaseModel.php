<?php
namespace db\model;
use \util\LogUtil;
use \util\TimeUtil;
use \util\ConfigUtil;

class BaseModel {
    protected $mysqli;
    
    public function __construct($dbName='spider') {
        $config = ConfigUtil::getConfig();
        $database = $config['databases'][$dbName];
        $this->mysqli = new \mysqli($database['host'], $database['user'], $database['passwd'], $dbName, $database['port']);
        if ($this->mysqli->connect_errno) {
            LogUtil::sqlError('the db connect failure! error_info: ' . $this->mysqli->connect_error);
            exit;
        }
        LogUtil::sqlConn('Database connection is successful!');
        $this->mysqli->set_charset('utf8');
    }

    public function query($sql) {
        $startTime = TimeUtil::getMsecTime();
        LogUtil::sql($sql);
        if ($this->mysqli->ping()) {
            LogUtil::sqlConn('the database connection is ok!');
        } else {
            LogUtil::sqlConn('the database connection is not ok. error_info: ' . $this->mysqli->error);
        }
        $mysqliResult = $this->mysqli->query($sql);
        if ($mysqliResult === false) {
            LogUtil::sqlError('the db query failure! error_info: ' . $this->mysqli->error);
            return false;
        }
        $endTime = TimeUtil::getMsecTime();
        $execTime = $endTime - $startTime;
        LogUtil::sqlTime('For sql execution time: ' . $execTime . 'ms. sql: ' . $sql);
        if ($mysqliResult === true) {
            return true;
        }
        $result = array();
        while ($row = $mysqliResult->fetch_array()) {
            $result[] = $row;
        }
        $mysqliResult->free();
        return $result;
    }

    public function close() {
        LogUtil::sqlConn('Database connection is closed!');
        $this->mysqli->close();
    }
}
