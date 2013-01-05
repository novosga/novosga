<?php
namespace core\util;

/**
 * Strings Utils
 *
 * @author rogeriolino
 */
class Strings {
    
    const REGEX_PARAMS = '/{([A-z0-9}]*)}/';
    
    public static function getParameters($str) {
        $matchs = array();
        preg_match_all(self::REGEX_PARAMS, $str, $matchs);
        return $matchs;
    }
    
    public static function format($str, array $args = array()) {
        foreach ($args as $k => $v) {
            $str = str_replace('{' . $k . '}', $v, $str);
        }
        return $str;
    }
    
    public static function doubleQuoteSlash($str) {
        return str_replace('"', '\"', $str);
    }
    
}
