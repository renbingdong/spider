<?php
/*
 * 日志操作类
 */
class LogUtil {

    private $log_file = "/Users/renbingdong/log/spider";

    public static function info($message) {
        $log_file = $log_file . "_" . date('Y-m-d', time()) . '.log';
        error_log($message . '\n', 3, $log_file);
    }
}
