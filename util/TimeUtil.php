<?php

class TimeUtil {
    
    public static function getMsecTime() {
        $now = microtime();
        list($usec, $sec) = explode(' ', $now);
        $result = (int)(($sec + $usec) * 1000);
        return $result;
    }
}
