<?php
namespace Novosga\Db;

use Novosga\Util\Arrays;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\XcacheCache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

/**
 * Classe DB
 */
class DatabaseConfig {
    
    protected $isDev;
    protected $conn;
    protected $em;
    protected $cacheDriver;
    
    public function __construct($prop = array(), $isDev = true) {
        if ($prop) {
            if (is_array($prop)) {
                $this->conn = $prop;
            } else {
                if (file_exists($prop)) {
                    $this->conn = require $prop;
                }
            }
        }
        $this->isDev = $isDev;
    }
    
    public function isIntalled() {
        return $this->get('driver') && $this->get('host');
    }
    
    public function set($name, $value) {
        $this->conn[$name] = $value;
    }
    
    public function get($name) {
        return Arrays::value($this->conn, $name, null);
    }
    
    public function values() {
        return $this->conn;
    }
    
    /**
     * @return EntityManager
     */
    public function createEntityManager() {
        if (!$this->em) {
            $paths = array(VENDOR_DIR . '/novosga/core/src/Novosga/Model');
            $config = Setup::createAnnotationMetadataConfiguration($paths, $this->isDev);
            $config->setAutoGenerateProxyClasses($this->isDev);
            $dir = sys_get_temp_dir();
            $config->setProxyDir($dir);
            // caching
            $this->cacheDriver = $this->createDoctrineCache($this->isDev);
            $config->setMetadataCacheImpl($this->cacheDriver);
            $config->setQueryCacheImpl($this->cacheDriver);
            $config->setResultCacheImpl($this->cacheDriver);
            $this->em = EntityManager::create($this->conn, $config);
        }
        return $this->em;
    }
    
    /**
     * Retorna o cacheDriver do Doctrine de acordo com a configuração da aplicação (dev/prod)
     * e com o driver disponível no servidor.
     * 
     * Quando $isDev é TRUE: 
     *     retorna ArrayCache
     * 
     * Quando $isDev é FALSE: 
     *     verifica, nessa ordem, se está disponível: APC e XCache.
     *     caso nenhum esteja disponível, retorna o ArrayCache
     * 
     * @return CacheProvider
     */
    private function createDoctrineCache($isDev) {
        if (!$isDev) {
            // APC
            if (extension_loaded('apc') && ini_get('apc.enabled')) {
                return new ApcCache();
            }
            // XCache
            else if (extension_loaded('xcache')) {
                return new XcacheCache();
            }
        }
        return new ArrayCache();
    }
    
}
