<?php

namespace Novosga\Util;

/**
 * DateUtil.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class DateUtil
{
    public static function now($format)
    {
        return date($format, self::getDate());
    }

    public static function nowSQL()
    {
        return self::now('Y-m-d H:i:s');
    }

    public static function format($date, $format, $onEmpty = '')
    {
        if (!empty($date)) {
            return date($format, strtotime($date));
        }

        return $onEmpty;
    }

    public static function formatToSQL($date)
    {
        return self::format(self::parseDate($date), 'Y-m-d H:i:s');
    }

    /**
     * diff = Time2 - Time1.
     *
     * @param type $time1
     * @param type $time2
     *
     * @return type
     */
    public static function diff($time1, $time2)
    {
        return strtotime($time2) - strtotime($time1);
    }

    public static function secToTime($seconds)
    {
        $hours = floor($seconds / 3600);
        $mins = floor(($seconds - ($hours * 3600)) / 60);
        $secs = floor(($seconds - ($hours * 3600) - ($mins * 60)));

        return str_pad($hours, 2, '0', STR_PAD_LEFT).':'.str_pad($mins, 2, '0', STR_PAD_LEFT).':'.str_pad($secs, 2, '0', STR_PAD_LEFT);
    }

    public static function timeToSec($time)
    {
        $t = explode(':', $time);
        if (sizeof($t) != 3) {
            throw new \Exception(_(sprintf('Formato de tempo inválido: %s', $time)));
        }
        $hours = (int) $t[0];
        $mins = (int) $t[1];
        $secs = (int) $t[2];

        return ($hours * 3600) + ($mins * 60) + $secs;
    }

    public static function parseDate($date)
    {
        // TODO: date i18n
        // pt-br
        return implode('-', array_reverse(explode('/', $date)));
    }

    public static function milis()
    {
        return self::getDate();
    }

    // centralizando origem da data para facilitar mudanca
    private static function getDate()
    {
        // date from php
        return time();
    }
}
