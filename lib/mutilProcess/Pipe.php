<?php

/**
 * 管道类  进程间通信
 */
class Pipe {
    private $fifo_name;     //管道名称
    private $w_pipe;        //管道写端
    private $r_pipe;        //管道读端

    public function __construct($pid, $name, $mode = 0666) {
        $fifo_name = "/Users/renbingdong/pipe/{$name}_pipe.{$pid}";
        if (file_exists($fifo_name)) {
            $this->fifo_name = $fifo_name;
            return;        
        }
        $result = posix_mkfifo($fifo_name, $mode);
        if (!$result) {
            echo "The fifo create to be failure. fifo: {$fifo_name}";
            die;        
        }
        $this->fifo_name = $fifo_name;
    }

    /**
     * 读管道
     */
    public function read() {
        if (!is_resource($this->r_pipe)) {
            $this->r_pipe = fopen($this->fifo_name, 'r');
        }
        $data = fgets($this->r_pipe);
        $data = trim($data);
        return $data;        
    }

    /**
     * 设置非阻塞读
     */
    public function setUnblock() {
        if (is_resource($this->r_pipe)) {
            stream_set_blocking($this->r_pipe, false);
        }
    }

    /**
     * 写管道
     */
    public function write($data) {
        if (!is_resource($this->w_pipe)) {
            $this->w_pipe = fopen($this->fifo_name, 'w');
        }
        $data = $data . PHP_EOL;
        fwrite($this->w_pipe, $data);
    }

    public function recycle() {
        if (is_resource($this->r_pipe)) {
            fclose($this->r_pipe);
        }
        if (is_resource($this->w_pipe)) {
            fclose($this->w_pipe);
        }
        unlink($this->fifo_name);            
    }
}
