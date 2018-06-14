<?php
namespace lib\multi;

class Worker {
    
    private $pid;
    private $pipe;      //管道
    private $consumer;  //消费任务

    public function __construct() {
        $this->pid = getmypid();
        $m2wPipe = new Pipe($this->pid, 'm2w');
        $this->pipe = array('m2worker' => $m2wPipe);               
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
            $m2wPipe = $this->pipe['m2worker'];
            $data = $m2wPipe->read();
            if (!empty($data)) {
                if (strpos($data, 'data:') === 0) {
                    $data = trim(substr($data, 5));
                    $this->_consumer($data);
                }        
            }
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
    
}
