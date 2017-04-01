<?php
/*
 * 爬虫脚本启动文件
 */

date_default_timezone_set("Asia/Shanghai");
require_once 'util/LogUtil.php';
require_once 'core/CrawlJob.php';

//爬虫默认遍历深度
$traverse_deep = 10;
//爬虫默认爬行URL
$main_url = "https://www.baidu.com";
if ($argc == 2) {
    $traverse_deep = $argv[0];
    $main_url = $argv[1];
}

//开始爬行数据
(new CrawlJob($main_url, $traverse_deep))->run();
