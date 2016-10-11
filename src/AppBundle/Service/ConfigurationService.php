<?php

namespace AppBundle\Service;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Processor;
use AppBundle\Config\NovosgaConfiguration;


class ConfigurationService
{

    private static $filename = __DIR__ . '/../../../app/config/novosga.yml';

    /**
     * Returns NovoSGA configuration
     * @return array
     */
    public static function get()
    {
        $configuration = [];

        if (!file_exists(self::$filename)) {
            file_put_contents(self::$filename, file_get_contents(self::$filename . '.dist'));
        }
        
        $config = Yaml::parse(file_get_contents(self::$filename));

        $processor = new Processor();
        $configuration = $processor->processConfiguration(
            new NovosgaConfiguration(),
            [$config]
        );

        return $configuration;
    }

    /**
     * Sets NovoSGA configuration
     */
    public static function set($configuration)
    {
        file_put_contents(self::$filename, Yaml::dump($configuration));
    }
}
