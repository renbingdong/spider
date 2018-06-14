<?php
namespace lib\mutilProcess;

/**
 * 进程池  维护worker进程信息
 */
class Pool {
    private $initSize;      //初始化worker进程数量
    private $pipePool;      //管道池
    private $conusmer;      //consumer任务

    public function __construct($initSize = 5) {
        $this->initSize = $initSize;        
        $this->pipePool = array();
    }

    public function setConsumer($consumer) {
        $this->consumer = $consumer;
    }

    /**
     * 初始化进程池
     */
    public function initPool() {
        LogUtil::process('Start creating process.');
        for ($i = 0; $i < $this->initSize; $i++) {
            $pid = pcntl_fork();
            if ($pid == -1) {
                LogUtil::process('Process creation failed.');
                continue;
            } elseif ($pid == 0) {
                $worker = new \lib\mutilProcess\Worker();
                $worker->setConsumer($this->consumer);
                $worker->run();
                exit;
            } else {
                Queue::freeWorker($pid);
                $m2wPipe = new Pipe($pid, 'm2w');
                $this->pipePool[$pid] = array('m2worker' => $m2wPipe);
                LogUtil::process("Process {$pid} creation success.");
            }
        }        
    }

    /**
     * 分配任务
     */
    public function dispatch($data) {
        $pid = $this->getFreeWorker();
        LogUtil::process("dispatch worker {$pid}, data: {$data}");
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
                return $pid
            } else {
                usleep(10);        
            }
        }
    }

    
    /**
     * 进程执行结束，回收进程
     */
    public function recyclePool() {
    }
}
