<?php
require_once __dir__ . DIRECTORY_SEPARATOR . 'Pipe.php';
require_once 'util/LogUtil.php';
require_once 'core/Process.php';

class Worker {
    
    private $pid;
    private $is_start;  //是否开始工作
    private $is_quit;   //当前进程是否退出
    private $pipe;      //管道
    private $consumer;  //消费任务

    public function __construct() {
        $this->pid = getmypid();
        $this->is_work = false;
        $this->is_quit = false;
        $m2w_pipe = new Pipe($this->pid, 'm2w');
        $w2m_pipe = new Pipe($this->pid, 'w2m');
        $this->pipe = array('m2worker' => $m2w_pipe, 'w2master' => $w2m_pipe);               
    }

    /**
     * 设置consumer
     * @param $consumer
     *      array('class' => '', 'method' => '')
     */
    public function setConsumer($consumer) {
        if (empty($consumer)) {
            LogUtil::process("The worker is not register the method of 'consumer'.");
            exit;
        }
        if (!method_exists($consumer['class'], $consumer['method'])) {
            LogUtil::process("The method of 'consumer' is not exists.");
            exit;
        }
        $this->consumer = $consumer;
    }

    public function run() {
        while (1) {
            $this->wait();
            $m2w_pipe = $this->pipe['m2worker'];
            $data = $m2w_pipe->read();
            if (!empty($data)) {
                if (strpos($data, 'data:') === 0) {
                    $data = trim(substr($data, 5));
                    $this->_consumer($data);
                }        
            }
            $command = "command:is_waiting";
            $w2m_pipe = $this->pipe['w2master'];
            $w2m_pipe->write($command);
            $this->is_start = false;
        }
                       
    }

    /**
     * consumer任务
     */
    private function _consumer($data) {
        $call_back = array();
        $call_back[] = $this->consumer['class'];
        $call_back[] = $this->consumer['method'];
        $res = call_user_func($call_back, $data);
        return $res;
    }
    
    /**
     * 进程阻塞，等待执行任务，或者结束进程
     */
    private function wait() {
        echo "worker {$this->pid} start wait\n";
        pcntl_signal(SIGUSR1, function(){
            $this->is_start = true;            
        });
        pcntl_signal(SIGUSR2, function(){
            $this->is_quit = true;
        });
        while (1) {
            pcntl_signal_dispatch();
            if ($this->is_start) {
                break;        
            }
            if ($this->is_quit) {
                LogUtil::process("End of the process {$this->pid} execution.");
                exit;
            }
            usleep(10);        
        }
        echo "worker {$this->pid} stop wait\n";
    }
    
}
