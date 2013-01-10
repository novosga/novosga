<?php
namespace core\util;

/**
 * DateUtil
 * @author rogeriolino
 */
class DateUtil {
    
    public static function now($format) {
        return date($format, self::getDate());
    }
    
    public static function nowSQL() {
        return self::now('Y-m-d H:i:s');
    }
    
    public static function format($date, $format) {
        return date($format, strtotime($date));
    }
    
    public static function formatToSQL($date) {
        return self::format($date, 'Y-m-d H:i:s');
    }
    
    public static function milis() {
        return self::getDate();
    }
    
    // centralizando origem da data para facilitar mudanca
    private static function getDate() {
        // date from php
        return time();
    }
    
}