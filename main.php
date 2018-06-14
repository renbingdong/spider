<?php
/*
 * 爬虫脚本启动文件
 */

date_default_timezone_set("Asia/Shanghai");

function autoload($class) {
    $file = str_replace('\\', '/', $class);
    $fileName = __DIR__ . '/' . $file;
    if (file_exists($fileName)) {
        require "{$fileName}.php";
    }
}
spl_autoload_register("autoload");

$dbConfig = require __DIR__ . '/config/db.php';
$otherConfig = require __DIR__ . '/config/config.php';
$config = array_merge($dbConfig, $otherConfig);


//爬虫默认遍历深度
$traverse_deep = 10;
//爬虫默认爬行URL
$main_url = "https://www.baidu.com";
if ($argc == 2) {
    $traverse_deep = $argv[0];
    $main_url = $argv[1];
}

//开始爬行数据
(new \core\CrawlJob($main_url, $traverse_deep))->run();
