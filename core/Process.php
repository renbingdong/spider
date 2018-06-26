<?php
namespace core;
use \util\LogUtil;
use \util\TimeUtil;
use \util\HttpClient;
use \util\FileUtil;

class Process {

    private static $maxDepth = 10;

    public static function getTask() {
        $message = CrawlQueue::getTask();
        return $message;
    }

    public static function consumer($message) {
        $startTime = TimeUtil::getMsecTime();
        self::_analysisUrl($message);
        $endTime = TimeUtil::getMsecTime();
        $execTime = $endTime - $startTime;
        LogUtil::infoTime('Total analysis time: ' . $execTime . 'ms. url: ' . $message);
    }

    /**
     * 解析访问URL
     * 保存URL内容，同时解析里面的子URL
     */
    private static function _analysisUrl($url) {
        $crawlPageModel = new \db\model\CrawlPageModel();
        $urlHashCode = md5($url);
        $isExist = $crawlPageModel->isExist($urlHashCode);
        if ($isExist) {
            return;
        }
        $contents = HttpClient::get($url);
        if (empty($contents)) {
            return;
        }
        $filePath = FileUtil::upload($contents);
        $summaryContext = self::_getSummaryContext($contents);
        if (!$summaryContext) {
            return;
        }
        $crawlPageModel->insert($url, $urlHashCode, $summaryContext, $filePath);
        $urlRegex = '/<a[^>]* href=\"(https:\/\/[^ ]*)\"/';
        $urlSub = array();
        $times = preg_match_all($urlRegex, $contents, $urlSub);
        if ($times == 0) {
            return;
        }
        $urlList = $urlSub[1];
        foreach ($urlList as $key => $url) {
            $urlArr = split('/', $url);
            if (!isset($urlArr[2]) || $urlArr[2] != 'blog.csdn.net') {
                unset($urlList[$key]);
            }
        }
        $urlList = array_value($urlList);
        CrawlQueue::pushTask($urlList);
    }

    private static function _getSummaryContext($contents) {
        $regexTitle = "/<title>(.*)<\/title>/";
        $cont = preg_match($regexTitle, $contents, $title);
        if (!$cont) {
            return false;
        }
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
