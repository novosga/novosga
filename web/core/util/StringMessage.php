<?php
namespace core\util;

/**
 * StringMessage
 *
 * @author rogeriolino
 */
class StringMessage {
    
    const REGEX_PARAMS = '/{([^}]*)}/';
    
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
    
}
