<?php

namespace Novosga\Util;

/**
 * FileUtils
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class FileUtils {
    
    public static function rm($dir) {
        if (!file_exists($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        return false;
    }
    
    public static function rmdir($dir) {
        $r = self::rm($dir);
        if (!$r) {
            foreach (scandir($dir) as $item) {
                if ($item == '.' || $item == '..') {
                    continue;
                }
                if (!self::rmdir($dir . DIRECTORY_SEPARATOR . $item)) {
                    return false;
                }
            }
            $r = rmdir($dir);
        }
        return $r;
    }
    
}
