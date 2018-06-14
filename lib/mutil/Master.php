<?php
namespace lib\mutil;
use \util\LogUtil;

class Master {
    private $pool;          //线程池
    private $hasNext;       //是否有下一个任务（任务是否完成）
    private $getTask;       //获取任务
    private $consumer;      //消费任务方法

    public function __construct($properties) {
        LogUtil::process('mutil-processing work start!');
        $this->_setProperties($properties);
        $this->init();
    }
    
    /**
     * 初始化工作
     */
    public function init() {
        $this->pool = new \lib\mutilProcess\Pool(10);
        $this->pool->setConsumer($this->consumer);
        $this->pool->initPool();
    }

    public function run() {
        while ($this->_hasNext()) {
            $data = $this->_getTask();
            $data = 'data:'. $data;
            $this->pool->dispatch($data);
        }
        $this->pool->recyclePool();
        LogUtil::process('mutil-processing work finish!');
    }

    /**
     * 设置进程工作任务
     * @param $properties
     *      array(
     *          'has_next' => array('class' => '', 'method' => ''),         //判断是否有下一个任务对应的回调方法
     *          'get_task' => array('class' => '', 'method' => ''),         //获取当前任务的回调方法
     *          'consumer' => array('class' => '', 'method' => '')          //执行任务的回调方法
     *      )
     */
    private function _setProperties($properties) {
        if (!isset($properties['has_next'])) {
            LogUtil::process("The master is not register the method of 'has_next'.");
            exit;
        }
        if (!method_exists($properties['has_next']['class'], $properties['has_next']['method'])) {
            LogUtil::process("The method of 'has_next' is not exists.");
            exit;
        }
        $this->has_next = $properties['has_next'];
        if (!isset($properties['get_task'])) {
            LogUtil::process("The master is not register the method of 'get_task'.");
            exit;
        }
        if (!method_exists($properties['get_task']['class'], $properties['get_task']['method'])) {
            LogUtil::process("The method of 'get_task' is not exists.");
            exit;
        }
        $this->get_task = $properties['get_task'];
        if (!isset($properties['consumer'])) {
            LogUtil::process("The master is not register the method of 'consumer'.");
            exit;
        }
        if (!method_exists($properties['consumer']['class'], $properties['consumer']['method'])) {
            LogUtil::process("The method of 'consumer' is not exists.");
            exit;
        }
        $this->consumer = $properties['consumer'];
    }

    /**
     * 检测是否有下一个任务
     * 如果有返回true，如果没有（即任务完成）返回false
     */
    private function _hasNext() {
        $call_back = array();
        $call_back[] = $this->has_next['class'];
        $call_back[] = $this->has_next['method'];
        $res = call_user_func($call_back);
        if (empty($res)) {
            return false;
        }
        return true;
    }

    /**
     * 获取当前任务
     */
    private function _getTask() {
        $call_back = array();
        $call_back[] = $this->get_task['class'];
        $call_back[] = $this->get_task['method'];
        $res = call_user_func($call_back);
        return $res;
    }
    
    public function destoryPool() {
        
    }
}
