<?php
$url = "https://www.baidu.com";
$opt = array(
    "ssl" => array(
        "verify_peer" => false,
        "verify_peer_name" => false,
    )
);
$resp = file_get_contents($url, false, stream_context_create($opt));
echo $resp;
