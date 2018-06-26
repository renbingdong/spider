<?php
/*
 * 爬虫脚本启动文件
 */

date_default_timezone_set("Asia/Shanghai");

function autoload($class) {
    $file = str_replace('\\', '/', $class);
    $fileName = __DIR__ . '/' . $file . '.php';
    if (file_exists($fileName)) {
        require "$fileName";
    }
}
spl_autoload_register("autoload");

$dbConfig = require __DIR__ . '/config/db.php';
$otherConfig = require __DIR__ . '/config/config.php';
$config = array_merge($dbConfig, $otherConfig);
\util\ConfigUtil::setConfig($config);


//爬虫默认爬行URL
$mainUrl = "https://blog.csdn.net/";
if ($argc == 2) {
    $mainUrl = $argv[1];
}

//开始爬行数据
(new \core\CrawlJob($mainUrl, 10))->run();
