<?php
require_once 'db/BaseModel.php';
require_once 'util/LogUtil.php';

class CrawlPageModel extends BaseModel {
    protected $table_name='crawl_page';

    public function __construct() {
        parent::__construct();
    }

    public function isExist($url_hash_code) {
        $sql = "select 1 from {$this->table_name} where url_hash_code = '{$url_hash_code}'";
        $result = $this->query($sql);
        if (empty($result)) {
            return false;
        }
        return true;
    }

    public function insert($url, $url_hash_code, $summary_context, $file_path) {
        $sql = "insert into {$this->table_name} (`url`, `url_hash_code`, `page_summary_context`, `page_file_path`, `c_t`) values ";
        $now = time();
        $sql .= "('{$url}', '{$url_hash_code}', '{$summary_context}', '{$file_path}', {$now})";
        $result = $this->query($sql);
        if (!$result) {
            LogUtil::info('the page insert failure! sql: ' . $sql);       
        }
        return $result;
    }

    public function __destruct() {
        $this->close();
    }
}
