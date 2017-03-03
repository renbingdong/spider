<?php
require_once 'util/LogUtil.php';

/*
 * 爬虫爬行链接处理类
 */
class CrawLJob {

    private $url;       //入口url
    private $max_deep;  //最大爬行深度
    private $url_queue; //爬行队列

    public function __construct($url, $max_deep) {
        $this->url = $url;
        $this->max_deep = $max_deep;
    }

    public function run()  {
        $contents = file_get_contents($this->url);
        if (strlen($contents) == 0) {
            LogUtil::info("url: {$this->url}  load failure!");       
            die(0);
        }
        $url_regex = '/<[a|A].*? href=([\'\"])([^#].*)\\1>/';
        $url_sub = array();
        preg_match_all($url_regex, $contents, $url_sub);
    
    }
}
