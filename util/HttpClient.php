<?php
namespace util;

/**
 * http请求处理类
 */
class HttpClient {

    public static function get($url) {
        $opts = array();
        $opts['ssl'] = array(
            'verify_peer' => false, 
            'verify_peer_name' => false
        );
        $opts['http'] = array(
            'method' => 'GET',
            'timeout' => 1,
        );
        $context = stream_context_create($opts);
        try {
            $content = file_get_contents($url, false, $context);
            if ($content === false) {
                LogUtil::info('url access error! url: ' . $url);
                return false;    
            }
            return $content;
        } catch (Exception $e) {
            LogUtil::info($e->getMessage());
            return false;
        }
    }
}
