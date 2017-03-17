<?php
require_once 'util/LogUtil.php';
require_once 'util/HttpClient.php';
require_once 'util/FileUtil.php';
require_once 'util/TimeUtil.php';
require_once 'db/model/CrawlPageModel.php';
require_once 'db/model/CrawlQueueModel.php';

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
        $url_info = array('deep_level' => 1, 'url_list' => array($this->url));
        $this->crawl_queue_model->batchInsert($url_info);   
        $this->_consumer();
    }

    /**
     * 爬虫遍历爬行方法
     */
    private function _consumer() {
        do {
            $start_time = TimeUtil::getMsecTime();
            $message = $this->crawl_queue_model->getOne();
            $this->crawl_queue_model->updateById($message['id']);
            $this->_analysisUrl($message['url'], $message['deep_level']);
            $end_time = TimeUtil::getMsecTime();
            $exec_time = $end_time - $start_time;
            LogUtil::info_time('Total analysis time: ' . $exec_time . 'ms. url: ' . $message['url']);
        } while (!empty($message));
        LogUtil::info("spider crawl finish!");
    }

    /**
     * 解析访问URL
     * 保存URL内容，同时解析里面的子URL
     */
    private function _analysisUrl($url, $level) {
        $url_hash_code = md5($url);
        $is_exist = $this->crawl_page_model->isExist($url_hash_code);
        if ($is_exist) {
            return;
        }
        $contents = HttpClient::get($url);
        if (strlen($contents) == 0) {
            return;
        }
        $file_path = FileUtil::upload($contents);
        $summary_context = $this->_getSummaryContext($contents);
        $this->crawl_page_model->insert($url, $url_hash_code, $summary_context, $file_path);
        if ($level >= $this->max_depth) {
            LogUtil::info("The spider has the maximum depth! ");
            return;
        }
        $url_regex = '/<[a|A][^>]* href=([\'\"])((?:http|https):\/\/[^ \'\"]*)\\1/';
        $url_sub = array();
        $times = preg_match_all($url_regex, $contents, $url_sub);
        if ($times == 0) {
            return;
        }
        $url_list = $url_sub[2];
        $url_info = array('deep_level' => $level + 1, 'url_list' => $url_list);
        $this->crawl_queue_model->batchInsert($url_info);
    }

    private function _getSummaryContext($contents) {
        $regex_title = "/<title>(.*)<\/title>/";
        preg_match($regex_title, $contents, $title);
        $summary_context = $title[1];
        $regex_charset = "/<meta[^>]+?charset=[^\w]?([-\w]+)/i";
        preg_match($regex_charset, $contents, $charset);
        if (!empty($charset)) {
            $summary_context = mb_convert_encoding($summary_context, 'utf-8', $charset);
        }
        return $summary_context;
    }
}
