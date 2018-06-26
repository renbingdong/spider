<?php
namespace util;

class ConfigUtil {

    private static $_config = array();

    public static function setConfig($config) {
        if (!is_array($config)) {
            throw new \Exception("config error.");
        }
        self::$_config = $config;
    }

    public static function getConfig() {
        return self::$_config;
    }
}
