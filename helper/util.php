<?php

if (!class_exists('AxcotoPixlrUtil')) {

    class AxcotoPixlrUtil {

        static public function redirect($title, $message, $page, $url) {
            include AxcotoPixlr::singleton()->pluginPath . '/templates/common/redirect.php';
        }

        static public function getPath($uri) {
            static $path;
            empty($path) && ($path = AxcotoPixlr::singleton()->pluginPath . $uri) ;
            return $path;
        }

        static public function e(&$value, $default='') {
            echo self::g($value, $default);
        }

        static public function overwrite($source, $target) {
            foreach ($source as $key=>$val) {
                if (!empty($target[$key])) {
                    $source[$key] = $target[$key];
                }
            }
            return $source;
        }

        static public function g(&$value, $default='') {
            if (isset($value)) {
                return $value;
            } else {
                return $default;
            }
        }

    }

}