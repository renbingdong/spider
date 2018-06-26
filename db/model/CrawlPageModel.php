<?php
namespace db\model;

class CrawlPageModel extends BaseModel {
    protected $table_name='crawl_page';

    public function __construct() {
        parent::__construct();
    }

    public function isExist($urlHashCode) {
        $sql = "select 1 from {$this->table_name} where url_hash_code = '{$urlHashCode}'";
        $result = $this->query($sql);
        if (empty($result)) {
            return false;
        }
        return true;
    }

    public function insert($url, $urlHashCode, $summaryContext, $filePath) {
        $sql = "insert into {$this->table_name} (`url`, `url_hash_code`, `page_summary_context`, `page_file_path`, `c_t`) values ";
        $now = time();
        $sql .= "('{$url}', '{$urlHashCode}', '{$summaryContext}', '{$filePath}', {$now})";
        $result = $this->query($sql);
        return $result;
    }

    public function get($params) {
        $sql = "select url_hash_code from {$this->table_name} where url_hash_code in ('" . join("','", $params['url_hash_list']) . "')";
        $result = $this->query($sql);
        return $result;
    }

    public function __destruct() {
        $this->close();
    }
}
