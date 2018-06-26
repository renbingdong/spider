<?php
namespace lib\mutil;
use \util\LogUtil;
use \util\ConfigUtil;

/**
 * 空闲工作进程列表
 */
class Queue {

    private static $_initSize = 0;     //初始化queue大小

    public static function initSize($size) {
        self::$_initSize = $size;
    }

    private static function getCache() {
        $config = ConfigUtil::getConfig();
        $file = $config['process_queue'];
        $pids = file_get_contents($file);
        if (empty($pids)) {
            return array();
        }
        $pidArr = explode(',', $pids);
        return $pidArr;
    }

    private static function setCache($pidArr) {
        $config = ConfigUtil::getConfig();
        $file = $config['process_queue'];
        $pids = join(',', $pidArr);
        $res = file_put_contents($file, $pids);
        if ($res === false) {
             LogUtil::process("get worker: write file failure. file:{$file}");
        }
    }

    public static function getWorker() {
        $pidArr = self::getCache();
        if (empty($pidArr)) {
            return false;
        }
        $pid = array_pop($pidArr);
        self::setCache($pidArr);
        return $pid;
    }

    public static function freeWorker($pid) {
        $pidArr = self::getCache();
        if (!in_array($pid, $pidArr)) {
            array_push($pidArr, $pid);
        }
        self::setCache($pidArr);
    }

    public static function isFree() {
        $pidArr = self::getCache();
        $count = count($pidArr);
        $size = self::$_initSize;
        echo "{$count}  {$size}\n";
        if ($count == $size) {
            LogUtil::process("process queue free fully. size: {$size} == count(): {$count}");
            return true;
        }
        return false;
    }

    public static function free() {
        $config = ConfigUtil::getConfig();
        $file = $config['process_queue'];
        $data = '';
        $res = file_put_contents($file, $data);
    }
}
