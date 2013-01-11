<?php
namespace core\db;

use \core\Config;

/**
 * Classe DB
 */
class DB {
    
    protected static $conn;
    protected static $em;
    
    public static function createConn($user, $pass, $host, $port, $dbname, $dbtype) {
        self::$conn = array(
            'user' => $user,
            'password' => $pass,
            'host' => $host,
            'dbname' => $dbname,
            'port' => $port,
        );
        /* 
         * previnindo problema com uso do SQL Server no linux. 
         * Doctrine so aceita o driver sqlsrv, porem o mesmo so eh para windows
         */
        if ($dbtype == 'mssql') {
            self::$conn['driverClass'] = 'Doctrine\DBAL\Driver\PDODblib\Driver';
        } else {
            self::$conn['driver'] = 'pdo_' . $dbtype;
        }
    }
    
    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public static function getEntityManager() {
        if (!self::$em) {
            if (!self::$conn) {
                self::createConn(Config::DB_USER, Config::DB_PASS, Config::DB_HOST, Config::DB_PORT, Config::DB_NAME, Config::DB_TYPE);
            }
            $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(self::$conn, Config::IS_DEV);
            $config->setAutoGenerateProxyClasses(Config::IS_DEV);
            self::$em = \Doctrine\ORM\EntityManager::create(self::$conn, $config);
        }
        return self::$em;
    }
    
}
