<?php
namespace lib\mutil;

class Worker {
    
    private $pid;
    private $pipe;              //管道

    public function __construct() {
        $this->pid = getmypid();
        $m2wPipe = new Pipe($this->pid, 'm2w');
        $this->pipe = array('m2worker' => $m2wPipe);               
    }

    public function run() {
        while (1) {
            $m2wPipe = $this->pipe['m2worker'];
            $data = $m2wPipe->read();
            if (!empty($data)) {
                if (strpos($data, 'data:') === 0) {
                    $data = trim(substr($data, 5));
                    $this->_consumer($data);
                } elseif (strpos($data, 'cmd:') === 0) {
                    $data = trim(substr($data, 4));
                    if ($data == 'stop') {
                        break;
                    }
                }        
            }
            Queue::freeWorker($this->pid);
        }
    }

    /**
     * consumer任务
     */
    private function _consumer($data) {
        $res = \core\Process::consumer($data);
        return $res;
    }
}
