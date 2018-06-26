<?php
namespace lib\mutil;
use \util\LogUtil;

/**
 * 进程池  维护worker进程信息
 */
class Pool {
    private $initSize;      //初始化worker进程数量
    private $pipePool;      //管道池
    private $pidPool;       //进程池

    public function __construct($initSize = 5) {
        $this->initSize = $initSize;        
        $this->pipePool = array();
        $this->pidPool = array();
    }

    /**
     * 初始化进程池
     */
    public function initPool() {
        LogUtil::process('Start creating process.');
        $processNum = 0;
        for ($i = 0; $i < $this->initSize; $i++) {
            $pid = pcntl_fork();
            if ($pid == -1) {
                LogUtil::process('Process creation failed.');
                continue;
            } elseif ($pid == 0) {
                $worker = new Worker();
                $worker->run();
                exit;
            } else {
                $processNum ++;
                Queue::freeWorker($pid);
                $m2wPipe = new Pipe($pid, 'm2w');
                $this->pipePool[$pid] = array('m2worker' => $m2wPipe);
                $this->pidPool[$pid] = $pid;
                LogUtil::process("Process {$pid} creation success.");
            }
        }
        Queue::initSize($processNum);
    }

    /**
     * 分配任务
     */
    public function dispatch($data) {
        $pid = $this->getFreeWorker();
        //LogUtil::process("dispatch worker {$pid}, data: {$data}");
        $m2wPipe = $this->pipePool[$pid]['m2worker'];
        $m2wPipe->write($data);    
    }
    
    /**
     * 获取当前可用的空闲进程
     */
    public function getFreeWorker() {
        while (1) {
            $pid = Queue::getWorker();
            if ($pid) {
                return $pid;
            } else {
                usleep(10);        
            }
        }
    }

    
    /**
     * 进程执行结束，回收进程
     */
    public function recyclePool() {
        //  回收worker信息
        foreach ($this->pidPool as $pid) {
             // 管道推送终止消息
             $m2wPipe = $this->pipePool[$pid]['m2worker'];
             $data = "cmd:stop";
             $m2wPipe->write($data);
             // 回收pipe资源
             usleep(10);
             $m2wPipe->recycle();
        }
        //  回收queue信息
        Queue::free();


        while (count($this->pidPool) > 0) {
            foreach ($this->pidPool as $key => $pid) {
                $res = pcntl_waitpid($pid, $status);
                if ($res == $pid) {
                    unset($this->pidPool[$key]);
                    LogUtil::process("process {$pid} stop.");
                }
            }
        }

    }
}
