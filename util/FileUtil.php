<?php
namespace util;

class FileUtil {

    private static function getFileDir() {
        $fileDir = $config['file_dir'];
        return $fileDir;
    }

    public static function upload($contents) {
        $startTime = TimeUtil::getMsecTime();
        $result = self::_upload($contents);
        $endTime = TimeUtil::getMsecTime();
        $uploadTime = $endTime - $startTime;
        LogUtil::fileTime('File upload time: ' . $uploadTime . 'ms. file_name: ' . $result);
        return $result;
    }

    private static function _upload($contents) {
        $fileDir = getFileDir();
        if (!is_dir($fileDir)) { 
            LogUtil::fileInfo("The default directory does not exist! dir: " . $fileDir);
            $isOk = mkdir($fileDir);
            if (!isOk) {
                LogUtil::fileInfo("Directory to create failure! dir: " . $fileDir);
                return '';
            }
        }
        $fileName = md5($contents);
        $firstDir = abs(crc32($fileName)) % 256;
        $secondDir = abs(crc32(md5($fileName))) % 1024;
        $currentDir = $fileDir . DIRECTORY_SEPARATOR . $firstDir;
        if (!is_dir($currentDir)) {
            LogUtil::fileInfo('Create the directory for the first time! dir: ' . $currentDir);
            $isOk = mkdir($currentDir);
            if (!isOk) {
                LogUtil::fileInfo('Directory to create failure! dir: ' . $currentDir);
                return '';
            }
        }
        $currentDir = $currentDir . DIRECTORY_SEPARATOR . $secondDir;
        if (!is_dir($currentDir)) {
            LogUtil::fileInfo('Create the directory for the first time! dir: ' . $currentDir);
            $isOk = mkdir($currentDir);
            if (!isOk) {
                LogUtil::fileInfo('Directory to create failure! dir: ' . $currentDir);
                return '';
            }
        }
        $fileAbsolutePath = $currentDir . DIRECTORY_SEPARATOR . $fileName . '.html';
        $fh = fopen($fileAbsolutePath, 'a+');
        $fLength = fwrite($fh, $contents);
        fclose($fh);
        if ($fLength === false) {
            LogUtil::fileInfo('File is written to failure! file_name: ' . $fileAbsolutePath);
            return '';
        }
        LogUtil::fileInfo('File to create successful! file_name: ' . $fileAbsolutePath);
        return $fileAbsolutePath;
    }
}
