<?php
/*
 * 日志操作类
 */
class LogUtil {

    private static $log_file = "/Users/renbingdong/log/spider";

    public static function info($message) {
        $log_file = self::$log_file . DIRECTORY_SEPARATOR . "spider_" . date('Y-m-d', time()) . '.log';
        $message = date('Y-m-d H:i:s') . ': ' . $message;
        error_log($message . PHP_EOL, 3, $log_file);
    }

    public static function info_time($message) {
        $log_file = self::$log_file . DIRECTORY_SEPARATOR . "spider_time_" . date('Y-m-d', time()) . '.log';
        $message = date('Y-m-d H:i:s') . ': ' . $message;
        error_log($message . PHP_EOL, 3, $log_file);
    }

    public static function sql($sql) {
        $log_file = self::$log_file . DIRECTORY_SEPARATOR . "spider_sql_" . date('Y-m-d', time()) . '.log';
        $message = date('Y-m-d H:i:s') . ': ' . $sql;
        error_log($message . PHP_EOL, 3, $log_file);
    }

    public static function sql_error($message) {
        $log_file = self::$log_file . DIRECTORY_SEPARATOR . "spider_sql_error_" . date('Y-m-d', time()) . '.log';
        $message = date('Y-m-d H:i:s') . ': ' . $message;
        error_log($message . PHP_EOL, 3, $log_file);
    }

    public static function sql_conn($message) {
        $log_file = self::$log_file . DIRECTORY_SEPARATOR . "spider_sql_conn_" . date('Y-m-d', time()) . '.log';
        $message = date('Y-m-d H:i:s') . ': ' . $message;
        error_log($message . PHP_EOL, 3, $log_file);
    }

    public static function sql_time($message) {
        $log_file = self::$log_file . DIRECTORY_SEPARATOR . "spider_sql_time_" . date('Y-m-d', time()) . '.log';
        $message = date('Y-m-d H:i:s') . ': ' . $message;
        error_log($message . PHP_EOL, 3, $log_file);
    }

    public static function file_info($message) {
        $log_file = self::$log_file . DIRECTORY_SEPARATOR . "spider_file_" . date('Y-m-d', time()) . '.log';
        $message = date('Y-m-d H:i:s') . ': ' . $message;
        error_log($message . PHP_EOL, 3, $log_file);
    }

    public static function file_time($message) {
        $log_file = self::$log_file . DIRECTORY_SEPARATOR . "spider_file_time_" . date('Y-m-d', time()) . '.log';
        $message = date('Y-m-d H:i:s') . ': ' . $message;
        error_log($message . PHP_EOL, 3, $log_file);
    }

    public static function process($message) {
        $log_file = self::$log_file . DIRECTORY_SEPARATOR . "spider_process_" . date('Y-m-d', time()) . '.log';
        $message = date('Y-m-d H:i:s') . ': ' . $message;
        error_log($message . PHP_EOL, 3, $log_file);
    }
}
