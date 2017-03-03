<?php
/*
 * 日志操作类
 */
class LogUtil {

    private $log_file = "/var/log/spider/spider";

    public static function info($message) {
        $log_file = $log_file . "_" . date('Y-m-d', time()) . '.log';
        error_log($log_file, 3, $message);
    }
}
