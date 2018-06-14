<?php
namespace core;
use \util\LogUtil;
use \util\HttpClient;
use \util\FileUtil;
use \util\TimeUtil;

/*
 * 爬虫爬行链接处理类
 */
class CrawlJob {

    private $url;                                       //入口url
    private $maxDepth;                                 //最大爬行深度

    public function __construct($url, $maxDepth) {
        $this->url = $url;
        $this->maxDepth = $maxDepth;
    }
    
    /**
     * 爬虫爬行入口方法
     */
    public function run()  {
        LogUtil::info("spider started, deep: {$this->maxDepth}, main_url: {$this->url}");
        $url_info = array('deep_level' => 1, 'url_list' => array($this->url));
        $crawlQueueModel = new \db\model\crawlQueueModel();
        $crawlQueueModel->batchInsert($url_info); 
        $properties = array(
            'has_next' => array('class' => 'Process', 'method' => 'hasNext'),
            'get_task' => array('class' => 'Process', 'method' => 'getTask'),
            'consumer' => array('class' => 'Process', 'method' => 'consumer'),
        );
        $master = new \lib\mutilProcess\Master($properties);
        sleep(5);
        $master->run();
        LogUtil::info("spider crawl finish!");
    }
}
