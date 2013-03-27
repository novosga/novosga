<?php
namespace core\util;

use \core\util\Objects;

/**
 * Arrays Utils
 *
 * @author rogeriolino
 */
class Arrays {
    
    public static function value($arr, $k, $d = '') {
        return (isset($arr) && isset($arr[$k])) ? $arr[$k] : $d;
    }
    
    public static function values($arr, array $keys) {
        $v = array();
        foreach ($keys as $k) {
            $v[] = self::value($arr, $k);
        }
        return $v;
    }
    
    public static function valuesToInt(array $arr) {
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $arr[$k] = self::valuesToInt($v);
            } else {
                $arr[$k] = (int) $v;
            }
        }
        return $arr;
    }
    
    public static function copy(array $arr) {
        $new = array();
        foreach ($arr as $k => $v) {
            $new[$k] = $v;
        }
        return $new;
    }
    
    public static function remove(array &$arr, $value) {
        $tmp = self::copy($arr);
        $arr = array();
        foreach ($tmp as $k => $v) {
            if ($v != $value) {
                $arr[$k] = $v;
            }
        }
    }
    
    public static function removeKey(array &$arr, $key) {
        $tmp = self::copy($arr);
        $arr = array();
        foreach ($tmp as $k => $v) {
            if ($k != $key) {
                $arr[$k] = $v;
            }
        }
    }
    
    public static function removeKeys(array &$arr, array $keys) {
        $tmp = self::copy($arr);
        $arr = array();
        foreach ($tmp as $k => $v) {
            $exists = false;
            foreach ($keys as $rk) {
                if ($k == $rk) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $arr[$k] = $v;
            }
        }
    }
    
    public static function toArray($value, $items = array(), $key = null) {
        if (is_array($value)) {
            $arr = array();
            foreach ($value as $v) {
                $a = self::toArray($v, $items, $key);
                if ($key == null) {
                    $arr[] = $a;
                } else {
                    $arr[Objects::get($v, $key)] = $a;
                }
            }
            return $arr;
        }
        if (is_object($value)) {
            if (sizeof($items)) {
                if (sizeof($items) > 1) {
                    $arr = array();
                    foreach ($items as $item) {
                        $arr[$item] = Objects::get($value, $item);
                    }
                    return $arr;
                } else {
                    return Objects::get($value, $items[0]);
                }
            }
            return $value . '';
        }
        return null;
    }
    
    public static function contains($arr, $value) {
        return in_array($value, $arr);
    }
    
}
