<?php

/**
 * 多进程处理日志
 */
class LogUtil {
    private static $log_dir = '';

    public static function info($message) {
        $log_file = self::$log_dir . DIRECTORY_SEPARATOR . "process_" . date('Y-m-d', time()) . '.log';
        $message = date('Y-m-d H:i:s') . ': ' . $message;
        error_log($message . PHP_EOL, 3, $log_file);
    }

}
