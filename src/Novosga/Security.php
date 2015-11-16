<?php

namespace Novosga;

/**
 * Security.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class Security
{
    public static function passEncode($pass)
    {
        return md5($pass);
    }
}
