<?php

namespace Novosga\Config;

/**
 * Api configuration file.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class ApiConfig extends ConfigFile
{
    private static $instance;

    /**
     * @param array $prop
     *
     * @return ApiConfig
     */
    public static function getInstance($prop = null)
    {
        if (!self::$instance) {
            self::$instance = new AppConfig($prop);
        }

        return self::$instance;
    }

    public function name()
    {
        return 'api.php';
    }

    /**
     * Extra  configuration.
     *
     * @return array
     */
    public function extra()
    {
        return \Novosga\Util\Arrays::value($this->values(), 'extra', array());
    }

    /**
     * Extra route configuration.
     *
     * @return array
     */
    public function routes()
    {
        return \Novosga\Util\Arrays::value($this->extra(), 'routes', array());
    }
}
