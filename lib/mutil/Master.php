<?php
namespace lib\mutil;
use \util\LogUtil;

class Master {
    private $pool;          //线程池

    public function __construct($processNum) {
        LogUtil::process('mutil-processing work start!');
        $this->init($processNum);
    }
    
    /**
     * 初始化工作
     */
    public function init($processNum) {
        $this->pool = new Pool($processNum);
        $this->pool->initPool();
    }

    public function run() {
        while (1) {
            $startTime = time();
            do {
                $data = $this->_getTask();
                if (empty($data)) {
                    $now = time();
                    if ($now - $startTime > 1000) {
                        break;
                    } else {
                        usleep(10);
                        continue;
                    }
                }
                $data = 'data:'. $data;
                $this->pool->dispatch($data);
            }while(1);
            sleep(10);
            if (Queue::isFree()) {
                break;
            }
        }
        $this->pool->recyclePool();
        LogUtil::process('mutil-processing work finish!');
    }

    /**
     * 获取当前任务
     */
    private function _getTask() {
        $res = \core\Process::getTask();
        return $res;
    }
    
}
