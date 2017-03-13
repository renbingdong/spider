<?php
require_once 'db/BaseModel.php';
require_once 'util/LogUtil.php';

class CrawlQueueModel extends BaseModel {
    protected $table_name='crawl_queue';

    public function __construct() {
        parent::__construct();
    }

    /**
     * 批量插入接口
     * @param $data
     *     array('deep_level'=> 1, url_list=>array('https://', 'http://'))
     */
    public function batchInsert($data) {
        if (empty($data['url_list'])) {
            return;
        }
        $sql = "insert into {$this->table_name} (`deep_level`, `url`, `is_crawl`, `status`, `c_t`, `u_t`) values ";
        $now = time();
        foreach ($data['url_list'] as $url) {
            $sql .= "({$data['deep_level']}, '{$url}', 0, 1, {$now}, {$now})";
            $sql .= ',';
        }
        $sql = rtrim($sql, ',');
        $result = $this->query($sql);
        return $result;
    }

    /**
     * 获取queue头部未爬行的消息（即id最小的未爬行记录）
     */
    public function getOne() {
        $sql = "select id, deep_level, url from {$this->table_name} where is_crawl = 0 order by id limit 1";
        $result = $this->query($sql);
        return current($result);
    }

    /**
     * 更新爬行队列信息
     */
    public function updateById($id) {
        $sql = "update {$this->table_name} set `is_crawl` = 1 where id = {$id}";
        return $this->query($sql);
    }

}
