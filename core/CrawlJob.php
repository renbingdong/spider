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
    private $processNum;                                //进程数量

    public function __construct($url, $processNum) {
        $this->url = $url;
        $this->processNum = $processNum;
    }
    
    /**
     * 爬虫爬行入口方法
     */
    public function run()  {
        LogUtil::info("spider started, process_num: {$this->processNum}, main_url: {$this->url}");
        $urlLst = array($this->url);
        CrawlQueue::pushTask($urlLst);
        $master = new \lib\mutil\Master($this->processNum);
        $master->run();
        LogUtil::info("spider crawl finish!");
    }
}
