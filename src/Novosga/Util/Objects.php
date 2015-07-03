<?php

namespace Novosga\Util;

use Exception;
use ReflectionClass;

/**
 * Objects utils.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class Objects
{
    /* setting */

    public static function set($obj, $props, $value)
    {
        if (is_array($props)) {
            foreach ($props as $prop => $value) {
                self::setSingle($obj, $prop, $value);
            }
        }
        self::setSingle($obj, $props, $value);
    }

    public static function setSingle($obj, $prop, $value)
    {
        if (is_array($obj)) {
            $obj[$prop] = $value;
        }
        if (is_object($obj)) {
            try {
                return self::setPropertyValue($obj, $prop, $value);
            } catch (Exception $e) {
                try {
                    return self::invokeMethod($obj, 'set'.ucfirst($prop), array($value));
                } catch (Exception $e) {
                    try {
                        return self::invokeMethod($obj, "set_$prop", array($value));
                    } catch (Exception $e) {
                    }
                }
            }
        }
        throw new Exception("Cannot set value of $prop in object ".get_class($obj));
    }

    public static function setPropertyValue($obj, $prop, $value)
    {
        $rc = new ReflectionClass($obj);
        while ($rc) {
            try {
                $rp = $rc->getProperty($prop);
                $rp->setAccessible(true);

                return $rp->setValue($obj, $value);
            } catch (Exception $e) {
                $rc = $rc->getParentClass();
            }
        }
        throw new Exception("Property $prop not found in object ".get_class($obj));
    }

    /* getting */

    public static function get($obj, $prop)
    {
        if (is_array($prop)) {
            return self::getValues($obj, $prop);
        }

        return self::getValue($obj, $prop);
    }

    public static function getValues($obj, array $props)
    {
        $values = array();
        exit();
        foreach ($props as $prop) {
            $values[$prop] = self::getValue($obj, $prop);
        }

        return $values;
    }

    public static function getValue($obj, $prop)
    {
        if (is_array($obj)) {
            return Arrays::value($obj, $prop, null);
        }
        if (is_object($obj)) {
            try {
                return self::getPropertyValue($obj, $prop);
            } catch (Exception $e) {
                try {
                    return self::invokeMethod($obj, 'get'.ucfirst($prop));
                } catch (Exception $e) {
                    try {
                        return self::invokeMethod($obj, "get_$prop");
                    } catch (Exception $e) {
                    }
                }
            }
        }
        throw new Exception("Cannot find value of $prop in object ".get_class($obj));
    }

    public static function getPropertyValue($obj, $prop)
    {
        $rc = new ReflectionClass($obj);
        while ($rc) {
            try {
                $rp = $rc->getProperty($prop);
                $rp->setAccessible(true);

                return $rp->getValue($obj);
            } catch (Exception $e) {
                $rc = $rc->getParentClass();
            }
        }
        throw new Exception("Property $prop not found in object ".get_class($obj));
    }

    /* method */

    public static function invokeMethod($obj, $method, array $params = array())
    {
        $rc = new ReflectionClass($obj);
        while ($rc) {
            try {
                $rm = $rc->getMethod($method);
                $rm->setAccessible(true);

                return $rm->invokeArgs($obj, $params);
            } catch (Exception $e) {
                $rc = $rc->getParentClass();
            }
        }
        throw new Exception("Method $method not found in object ".get_class($obj));
    }
}
