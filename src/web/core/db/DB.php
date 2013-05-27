<?php
namespace core\db;

use \core\Config;

/**
 * Classe DB
 */
class DB {
    
    protected static $conn;
    protected static $cacheDriver;
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
            $config->setAutoGenerateProxyClasses(true);
            $dir = sys_get_temp_dir();
            $config->setProxyDir($dir);
            // caching
            self::$cacheDriver = self::createDoctrineCache();
            $config->setMetadataCacheImpl(self::$cacheDriver);
            $config->setQueryCacheImpl(self::$cacheDriver);
            $config->setResultCacheImpl(self::$cacheDriver);
            self::$em = \Doctrine\ORM\EntityManager::create(self::$conn, $config);
            // alterando o formato padrao do datetime do sql server
            if (Config::DB_TYPE == 'mssql') {
                self::$em->getConnection()->exec('SET DATEFORMAT ymd');
            }
        }
        return self::$em;
    }
    
    /**
     * Retorna o cacheDriver do Doctrine de acordo com a configuração da aplicação (dev/prod)
     * e com o driver disponível no servidor.
     * 
     * Quando IS_DEV é TRUE: 
     *     retorna ArrayCache
     * 
     * Quando IS_DEV é FALSE: 
     *     verifica, nessa ordem, se está disponível: APC e XCache.
     *     caso nenhum esteja disponível, retorna o ArrayCache
     * 
     * @return \Doctrine\Common\Cache\ArrayCache
     */
    private static function createDoctrineCache() {
        if (!Config::IS_DEV) {
            // APC
            if (extension_loaded('apc') && ini_get('apc.enabled')) {
                return new \Doctrine\Common\Cache\ApcCache();
            }
            // XCache
            else if (extension_loaded('xcache')) {
                return new \Doctrine\Common\Cache\XcacheCache();
            }
        }
        return new \Doctrine\Common\Cache\ArrayCache();
    }
    
}
