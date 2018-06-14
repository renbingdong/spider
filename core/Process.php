<?php
namespace core;
use \util\LogUtil;
use \util\TimeUtil;
use \util\HttpClient;

class Process {

    private static $maxDepth = 10;

    public static function hasNext() {
        $crawlQueueModel = new \db\model\CrawlQueueModel();
        $times = 0;
        do {
            $res = $crawlQueueModel->hasNext();
            if ($res) {
                return $res;
            }
            $times ++;
            sleep(1);
        } while($times <= 3);
       return false;  
    }

    public static function getTask() {
        $crawlQueueModel = new CrawlQueueModel();
        $message = $crawlQueueModel->getOne();
        $crawlQueueModel->updateById($message['id']);
        return json_encode($message);
    }

    public static function consumer($data) {
        $message = json_decode($data, true);
        $startTime = TimeUtil::getMsecTime();
        self::_analysisUrl($message['url'], $message['deep_level']);
        $endTime = TimeUtil::getMsecTime();
        $execTime = $endTime - $startTime;
        LogUtil::infoTime('Total analysis time: ' . $execTime . 'ms. url: ' . $message['url']);
    }

    /**
     * 解析访问URL
     * 保存URL内容，同时解析里面的子URL
     */
    private static function _analysisUrl($url, $level) {
        $crawlPageModel = new \db\model\CrawlPageModel();
        $crawlQueueModel = new \db\mode\CrawlQueueModel();
        $urlHashCode = md5($url);
        $isExist = $crawlPageModel->isExist($urlHashCode);
        if ($isExist) {
            return;
        }
        $contents = HttpClient::get($url);
        if (strlen($contents) == 0) {
            return;
        }
        $filePath = FileUtil::upload($contents);
        $summaryContext = self::_getSummaryContext($contents);
        $crawlPageModel->insert($url, $urlHashCode, $summaryContext, $filePath);
        if ($level >= self::$maxDepth) {
            LogUtil::info("The spider has the maximum depth! ");
            return;
        }
        $urlRegex = '/<[a|A][^>]* href=([\'\"])((?:http|https):\/\/[^ \'\"]*)\\1/';
        $urlSub = array();
        $times = preg_match_all($urlRegex, $contents, $urlSub);
        if ($times == 0) {
            return;
        }
        $urlList = $urlSub[2];
        $urlInfo = array('deep_level' => $level + 1, 'url_list' => $urlList);
        $crawlQueueModel->batchInsert($urlInfo);
    }

    private static function _getSummaryContext($contents) {
        $regexTitle = "/<title>(.*)<\/title>/";
        preg_match($regexTitle, $contents, $title);
        $summaryContext = $title[1];
        $regexCharset = "/<meta[^>]+?charset=[^\w]?([-\w]+)/i";
        preg_match($regexCharset, $contents, $charset);
        if (!empty($charset)) {
            $summaryContext = mb_convert_encoding($summaryContext, 'utf-8', $charset);
        }
        $summaryContext = addslashes($summaryContext);
        return $summaryContext;
    }
}
