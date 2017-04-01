<?php
require_once __dir__ . DIRECTORY_SEPARATOR . 'Worker.php';
require_once __dir__ . DIRECTORY_SEPARATOR . 'Pipe.php';
require_once 'util/LogUtil.php';

/**
 * 进程池  维护worker进程信息
 */
class Pool {
    private $max_size;      //最大worker进程数量
    private $pool;          //进程池
    private $free_pool;     //空闲进程池
    private $busy_pool;     //工作进程池
    private $pipe_pool;     //管道池
    private $conusmer;      //consumer任务

    public function __construct($max_size = 5) {
        $this->max_size = $max_size;        
        $this->pool = array();
        $this->free_pool = array();
        $this->busy_pool = array();
        $this->pipe_pool = array();
    }

    public function setConsumer($consumer) {
        $this->consumer = $consumer;
    }

    /**
     * 初始化进程池
     */
    public function initPool() {
        LogUtil::process('Start creating process.');
        for ($i = 0; $i < $this->max_size; $i++) {
            $pid = pcntl_fork();
            if ($pid == -1) {
                LogUtil::process('Process creation failed.');
                continue;
            } elseif ($pid == 0) {
                $worker = new Worker();
                $worker->setConsumer($this->consumer);
                $worker->run();
                exit();
            } else {
                $this->pool[$pid] = $pid;
                $this->free_pool[] = $pid;
                $m2w_pipe = new Pipe($pid, 'm2w');
                $w2m_pipe = new Pipe($pid, 'w2m');
                $w2m_pipe->setUnblock();
                $this->pipe_pool[$pid] = array('m2worker' => $m2w_pipe, 'w2master' => $w2m_pipe);
                LogUtil::process("Process {$pid} creation success.");
            }
        }        
    }

    /**
     * 分配任务
     */
    public function dispatch($data) {
        echo "准备获取空闲worker\n";
        $pid = $this->getFreeWorker();
        echo "获取到空闲worker{$pid}\n";
        posix_kill($pid, SIGUSR1);
        LogUtil::process("dispatch worker {$pid}, data: {$data}");
        $m2w_pipe = $this->pipe_pool[$pid]['m2worker'];
        $m2w_pipe->write($data);    
    }
    
    /**
     * 获取当前可用的空闲进程
     */
    public function getFreeWorker() {
        while (1) {
            if (!empty($this->free_pool)) {
                $pid = array_shift($this->free_pool);
                array_push($this->busy_pool, $pid);
                return $pid;
            }
            $this->_refresh();
            usleep(10);        
        }
    }

    /**
     * 刷新进程池
     */
    private function _refresh() {
        foreach ($this->busy_pool as $key => $pid) {
            $w2m_pipe = $this->pipe_pool[$pid]['w2master'];
            $data = $w2m_pipe->read();
            if (!empty($data)) {
                if (strpos($data, 'command:') === 0) {
                    $command = trim(substr($data, 8));
                    if ($command == 'is_waiting') {
                        array_push($this->free_pool, $pid);
                        unset($this->busy_pool[$key]);
                    }        
                }        
            }        
        }
        $this->busy_pool = array_values($this->busy_pool);        
    }
    
    /**
     * 进程执行结束，回收进程
     */
    public function recyclePool() {
        while (1) {
            if (count($this->busy_pool) === 0 && count($this->free_pool) === 0) {
                break;
            }
            if (count($this->free_pool) === 0) {
                $this->_refresh();
                usleep(100);
                continue;
            }
            foreach ($this->free_pool as $key => $pid) {
                posix_kill($pid, SIGUSR2);
                unset($this->free_pool[$key]);
                $pipe_list = $this->pipe_pool[$pid];
                foreach ($pipe_list as $pipe) {
                    $pipe->recycle();
                }
            }
        }
        while (count($this->pool) > 0) {
            foreach ($this->pool as $key => $pid) {
                $res = pcntl_waitpid($pid, $status);
                if ($res == $pid) {
                    unset($this->pool[$key]);
                }
            }
        }
    }
}
