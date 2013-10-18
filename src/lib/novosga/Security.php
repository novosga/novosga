<?php
namespace novosga;

/**
 * Security
 *
 * @author rogeriolino
 */
class Security {
    
    public static function passEncode($pass) {
        return md5($pass);
    }
    
}
