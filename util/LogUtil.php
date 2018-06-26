<?php
namespace util;

/*
 * 日志操作类
 */
class LogUtil {

    private static function getLogFile() {
        $config = ConfigUtil::getConfig();
        $logFile = $config['log_file'];
        return $logFile;
    }
    public static function info($message) {
        $logFile = self::getLogFile() . DIRECTORY_SEPARATOR . "spider_" . date('Y-m-d', time()) . '.log';
        $message = date('Y-m-d H:i:s') . ': ' . $message;
        error_log($message . PHP_EOL, 3, $logFile);
    }

    public static function infoTime($message) {
        $logFile = self::getLogFile() . DIRECTORY_SEPARATOR . "spider_time_" . date('Y-m-d', time()) . '.log';
        $message = date('Y-m-d H:i:s') . ': ' . $message;
        error_log($message . PHP_EOL, 3, $logFile);
    }

    public static function sql($sql) {
        $logFile = self::getLogFile() . DIRECTORY_SEPARATOR . "spider_sql_" . date('Y-m-d', time()) . '.log';
        $message = date('Y-m-d H:i:s') . ': ' . $sql;
        error_log($message . PHP_EOL, 3, $logFile);
    }

    public static function sqlError($message) {
        $logFile = self::getLogFile() . DIRECTORY_SEPARATOR . "spider_sql_error_" . date('Y-m-d', time()) . '.log';
        $message = date('Y-m-d H:i:s') . ': ' . $message;
        error_log($message . PHP_EOL, 3, $logFile);
    }

    public static function sqlConn($message) {
        $logFile = self::getLogFile() . DIRECTORY_SEPARATOR . "spider_sql_conn_" . date('Y-m-d', time()) . '.log';
        $message = date('Y-m-d H:i:s') . ': ' . $message;
        error_log($message . PHP_EOL, 3, $logFile);
    }

    public static function sqlTime($message) {
        $logFile = self::getLogFile() . DIRECTORY_SEPARATOR . "spider_sql_time_" . date('Y-m-d', time()) . '.log';
        $message = date('Y-m-d H:i:s') . ': ' . $message;
        error_log($message . PHP_EOL, 3, $logFile);
    }

    public static function fileInfo($message) {
        $logFile = self::getLogFile() . DIRECTORY_SEPARATOR . "spider_file_" . date('Y-m-d', time()) . '.log';
        $message = date('Y-m-d H:i:s') . ': ' . $message;
        error_log($message . PHP_EOL, 3, $logFile);
    }

    public static function fileTime($message) {
        $logFile = self::getLogFile() . DIRECTORY_SEPARATOR . "spider_file_time_" . date('Y-m-d', time()) . '.log';
        $message = date('Y-m-d H:i:s') . ': ' . $message;
        error_log($message . PHP_EOL, 3, $logFile);
    }

    public static function process($message) {
        $logFile = self::getLogFile() . DIRECTORY_SEPARATOR . "spider_process_" . date('Y-m-d', time()) . '.log';
        $message = date('Y-m-d H:i:s') . ': ' . $message;
        error_log($message . PHP_EOL, 3, $logFile);
    }
}
