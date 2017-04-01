<?php
require_once 'util/LogUtil.php';
require_once 'util/HttpClient.php';
require_once 'util/FileUtil.php';
require_once 'util/TimeUtil.php';
require_once 'db/model/CrawlPageModel.php';
require_once 'db/model/CrawlQueueModel.php';
require_once 'lib/mutilProcess/Master.php';

/*
 * 爬虫爬行链接处理类
 */
class CrawLJob {

    private $url;                                       //入口url
    private $max_depth;                                 //最大爬行深度
    private $crawl_page_model;
    private $crawl_queue_model;

    public function __construct($url, $max_depth) {
        $this->url = $url;
        $this->max_depth = $max_depth;
        $this->crawl_page_model = new CrawlPageModel();
        $this->crawl_queue_model = new CrawlQueueModel();
    }
    
    /**
     * 爬虫爬行入口方法
     */
    public function run()  {
        LogUtil::info("spider started, deep: {$this->max_depth}, main_url: {$this->url}");
        $url_info = array('deep_level' => 1, 'url_list' => array($this->url));
        $this->crawl_queue_model->batchInsert($url_info); 
        $properties = array(
            'has_next' => array('class' => 'Process', 'method' => 'hasNext'),
            'get_task' => array('class' => 'Process', 'method' => 'getTask'),
            'consumer' => array('class' => 'Process', 'method' => 'consumer'),
        );
        $master = new Master($properties); 
        sleep(5);
        $master->run();
        LogUtil::info("spider crawl finish!");
    }
}
