<?php
namespace core;
use \util\ConfigUtil;

class CrawlQueue {

    private static function getCache() {
        $config = ConfigUtil::getConfig();
        $file = $config['data_queue'];
        $datas = file_get_contents($file);
        if (empty($datas)) {
            return array();
        }
        $dataArr = explode(',', $datas);
        return $dataArr;
    }

    private static function setCache($dataArr) {
        $config = ConfigUtil::getConfig();
        $file = $config['data_queue'];
        $datas = join(',', $dataArr);
        $res = file_put_contents($file, $datas);
        if ($res === false) {
            LogUtil::info("crawlQueue push failure.");
        }
    }

    public static function getTask() {
        $dataArr = self::getCache();
        if (empty($dataArr)) {
            return false;
        }
        $data = array_pop($dataArr);
        self::setCache($dataArr);
        return urldecode($data);
    }

    public static function pushTask($urlLst) {
        if (count($urlLst) > 20) {
            $urlLst = array_slice($urlLst, 0, 20);
        }
        $urlInfo = array();
        foreach ($urlLst as $url) {
            $key = md5($url);
            $urlInfo[$key] = urlencode($url);
        }
        $params = array();
        $params['url_hash_list'] = array_keys($urlInfo);
        $crawlPageModel = new \db\model\CrawlPageModel();
        $result = $crawlPageModel->get($params);
        if (!empty($result)) {
            foreach ($result as $row) {
                if (isset($urlInfo[$row['url_hash_code']])) {
                    unset($urlInfo[$row['url_hash_code']]);
                }
            }
        }
        $urlLst = array_values($urlInfo);
        $dataArr = self::getCache();
        if (count($dataArr) < 1000) {
            $dataArr = array_merge($dataArr, $urlLst);
            self::setCache($dataArr);
        }
    }
} 
