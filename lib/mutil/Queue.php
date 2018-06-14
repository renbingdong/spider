<?php
namespace lib/mutil;

/**
 * 空闲工作进程列表
 */
class Queue {

    private static $_queue = array();

    public static function getWorker() {
        if (empty(self::$_queue)) {
            return false;
        }
        return array_pop(self::$_queue);
    }

    public static function freeWorker($pid) {
        if (!in_array($pid, self::$_queue)) {
            array_push(self::$_queue, $pid);
        }
    }

}
