<?php
namespace core\db;

use \core\Config;
use \core\util\Arrays;

/**
 * Classe DB
 */
class DB {

    protected static $em;
    
    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public static function getEntityManager() {
        if (!self::$em) {
            $conn = array(
                'user' => Config::DB_USER,
                'password' => Config::DB_PASS,
                'host' => Config::DB_HOST,
                'dbname' => Config::DB_NAME,
            );
            /* 
             * previnindo problema com uso do SQL Server no linux. 
             * Doctrine so aceita o driver sqlsrv, porem o mesmo so e para windows
             */
            if (Config::DB_TYPE == 'mssql') {
                $conn['driverClass'] = 'Doctrine\DBAL\Driver\PDODblib\Driver';
            } else {
                $conn['driver'] = 'pdo_' . Config::DB_TYPE;
            }
            $isDev = true;
            $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration($conn, $isDev);
            $config->setAutoGenerateProxyClasses($isDev);
            self::$em = \Doctrine\ORM\EntityManager::create($conn, $config);
        }
        return self::$em;
    }
    
}
