<?php
require_once 'util/LogUtil.php';
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
    private $file_dir = '/Users/renbingdong/Page';      //默认文件存储地址

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
            $message = $this->crawl_queue_model->getOne();
            $this->crawl_queue_model->updateById($message['id']);
            $this->_analysisUrl($message['url'], $message['deep_level']);
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
        $opt = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            )
        );
        $contents = file_get_contents($url, false, stream_context_create($opt));
        if (strlen($contents) == 0) {
            $this->crawl_page_model->insert($url, $url_hash_code, '', '');
            return;
        }
        $file_path = $this->_uploadPage($contents);
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

    private function _uploadPage($contents) {
        if (!is_dir($this->file_dir)) {
            LogUtil::file_info("The default directory does not exist! dir: " . $this->file_dir);
            $is_ok = mkdir($this->file_dir);
            if (!is_ok) {
                LogUtil::file_info("Directory to create failure! dir: " . $this->file_dir);
                return '';
            }
        }
        $file_name = md5($contents);
        $first_dir = abs(crc32($file_name)) % 256;
        $current_dir = $this->file_dir . DIRECTORY_SEPARATOR . $first_dir;
        if (!is_dir($current_dir)) {
            LogUtil::file_info('Create the directory for the first time! dir: ' . $current_dir);
            $is_ok = mkdir($current_dir);
            if (!is_ok) {
                LogUtil::file_info('Directory to create failure! dir: ' . $current_dir);
                return '';
            }
        }
        $file_absolute_path = $current_dir . DIRECTORY_SEPARATOR . $file_name . '.html';
        $fh = fopen($file_absolute_path, 'a+');
        $f_length = fwrite($fh, $contents);
        fclose($fh);
        if ($f_length === false) {
            LogUtil::file_info('File is written to failure! file_name: ' . $file_absolute_path);
            return '';
        }
        LogUtil::file_info('File to create successful! file_name: ' . $file_absolute_path);
        return $file_absolute_path;
    }

    private function _getSummaryContext($contents) {
        $regex_title = "/<title>(.*)<\/title>/";
        preg_match($regex_title, $contents, $title);
        return $title[1];
    }
}
