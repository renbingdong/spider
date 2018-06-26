<?php
namespace lib\mutil;
use \util\ConfigUtil;

/**
 * 管道类  进程间通信
 */
class Pipe {
    private $fifoName;     //管道名称
    private $wPipe;        //管道写端
    private $rPipe;        //管道读端

    public function __construct($pid, $name, $mode = 0666) {
        $config = ConfigUtil::getConfig();
        $fifoName = $config['pipe'] . "/{$name}_pipe.{$pid}";
        if (file_exists($fifoName)) {
            $this->fifoName = $fifoName;
            return;        
        }
        $result = posix_mkfifo($fifoName, $mode);
        if (!$result) {
            echo "The fifo create to be failure. fifo: {$fifoName}";
            die;        
        }
        $this->fifoName = $fifoName;
    }

    /**
     * 读管道
     */
    public function read() {
        if (!is_resource($this->rPipe)) {
            $this->rPipe = fopen($this->fifoName, 'r');
        }
        $data = fgets($this->rPipe);
        $data = trim($data);
        return $data;        
    }

    /**
     * 写管道
     */
    public function write($data) {
        if (!is_resource($this->wPipe)) {
            $this->wPipe = fopen($this->fifoName, 'w');
        }
        $data = $data . PHP_EOL;
        fwrite($this->wPipe, $data);
    }

    public function recycle() {
        if (is_resource($this->rPipe)) {
            fclose($this->rPipe);
        }
        if (is_resource($this->wPipe)) {
            fclose($this->wPipe);
        }
        unlink($this->fifoName);            
    }
}
