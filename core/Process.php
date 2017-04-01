<?php
require_once 'db/model/CrawlPageModel.php';
require_once 'db/model/CrawlQueueModel.php';
require_once 'util/LogUtil.php';
require_once 'util/HttpClient.php';
require_once 'util/FileUtil.php';
require_once 'util/TimeUtil.php';

class Process {

    private static $max_depth = 10;

    public static function hasNext() {
        $crawl_queue_model = new CrawlQueueModel();
        $times = 0;
        do {
            $res = $crawl_queue_model->hasNext();
            if ($res) {
                return $res;
            }
            $times ++;
            sleep(1);
        } while($times <= 3);
       return false;  
    }

    public static function getTask() {
        $crawl_queue_model = new CrawlQueueModel();
        $message = $crawl_queue_model->getOne();
        $crawl_queue_model->updateById($message['id']);
        return json_encode($message);
    }

    public static function consumer($data) {
        $message = json_decode($data, true);
        $start_time = TimeUtil::getMsecTime();
        self::_analysisUrl($message['url'], $message['deep_level']);
        $end_time = TimeUtil::getMsecTime();
        $exec_time = $end_time - $start_time;
        LogUtil::info_time('Total analysis time: ' . $exec_time . 'ms. url: ' . $message['url']);
    }

    /**
     * 解析访问URL
     * 保存URL内容，同时解析里面的子URL
     */
    private static function _analysisUrl($url, $level) {
        $crawl_page_model = new CrawlPageModel();
        $crawl_queue_model = new CrawlQueueModel();
        $url_hash_code = md5($url);
        $is_exist = $crawl_page_model->isExist($url_hash_code);
        if ($is_exist) {
            return;
        }
        $contents = HttpClient::get($url);
        if (strlen($contents) == 0) {
            return;
        }
        $file_path = FileUtil::upload($contents);
        $summary_context = self::_getSummaryContext($contents);
        $crawl_page_model->insert($url, $url_hash_code, $summary_context, $file_path);
        if ($level >= self::$max_depth) {
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
        $crawl_queue_model->batchInsert($url_info);
    }

    private static function _getSummaryContext($contents) {
        $regex_title = "/<title>(.*)<\/title>/";
        preg_match($regex_title, $contents, $title);
        $summary_context = $title[1];
        $regex_charset = "/<meta[^>]+?charset=[^\w]?([-\w]+)/i";
        preg_match($regex_charset, $contents, $charset);
        if (!empty($charset)) {
            $summary_context = mb_convert_encoding($summary_context, 'utf-8', $charset);
        }
        $summary_context = addslashes($summary_context);
        return $summary_context;
    }
}
