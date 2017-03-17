<?php
require_once 'util/LogUtil.php';
require_once 'util/TimeUtil.php';

class FileUtil {

    private static $file_dir = '/Users/renbingdong/Page';

    public static function upload($contents) {
        $start_time = TimeUtil::getMsecTime();
        $result = self::_upload($contents);
        $end_time = TimeUtil::getMsecTime();
        $upload_time = $end_time - $start_time;
        LogUtil::file_time('File upload time: ' . $upload_time . 'ms. file_name: ' . $result);
        return $result;
    }

    private static function _upload($contents) {
        if (!is_dir(self::$file_dir)) { 
            LogUtil::file_info("The default directory does not exist! dir: " . self::$file_dir);
            $is_ok = mkdir(self::$file_dir);
            if (!is_ok) {
                LogUtil::file_info("Directory to create failure! dir: " . self::$file_dir);
                return '';
            }
        }
        $file_name = md5($contents);
        $first_dir = abs(crc32($file_name)) % 256;
        $second_dir = abs(crc32(md5($file_name))) % 1024;
        $current_dir = self::$file_dir . DIRECTORY_SEPARATOR . $first_dir;
        if (!is_dir($current_dir)) {
            LogUtil::file_info('Create the directory for the first time! dir: ' . $current_dir);
            $is_ok = mkdir($current_dir);
            if (!is_ok) {
                LogUtil::file_info('Directory to create failure! dir: ' . $current_dir);
                return '';
            }
        }
        $current_dir = $current_dir . DIRECTORY_SEPARATOR . $second_dir;
        if (!is_dir($current_dir)) {
            LogUtil::file_info('Create the directory for the first time! dir: ' . $current_dir);
            $is_ok = mkdir($current_dir);
            if (!is_ok) {
                LogUtil::file_info('Directory to create failure! dir: ' . $current_dir);
                return '';
            }
        }
        $file_absolute_path = $current_dir . DIRECTORY_SEPARATOR . $file_name . '.html';
        $fh = fopen($file_absolute_path, 'a+');
        $f_length = fwrite($fh, $contents);
        fclose($fh);
        if ($f_length === false) {
            LogUtil::file_info('File is written to failure! file_name: ' . $file_absolute_path);
            return '';
        }
        LogUtil::file_info('File to create successful! file_name: ' . $file_absolute_path);
        return $file_absolute_path;
    }
}
