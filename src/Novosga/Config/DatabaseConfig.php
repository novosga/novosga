<?php

namespace Novosga\Config;

use Novosga\Config\ConfigFile;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\XcacheCache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

/**
 * Database configuration.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class DatabaseConfig extends ConfigFile
{
    protected $em;
    protected $cacheDriver;
    protected $isDev = false;

    private static $instance;

    /**
     * @param array $prop
     *
     * @return DatabaseConfig
     */
    public static function getInstance($prop = null)
    {
        if (!self::$instance) {
            self::$instance = new self($prop);
        }

        return self::$instance;
    }

    public function isIntalled()
    {
        return ($this->get('driver') || $this->get('driverClass')) && $this->get('host');
    }

    public function name()
    {
        return 'database.php';
    }

    public function isDev()
    {
        return $this->isDev;
    }

    public function setDev($isDev)
    {
        $this->isDev = $isDev;
    }

    /**
     * @return EntityManager
     */
    public function createEntityManager()
    {
        if (!$this->em) {
            $paths = array(NOVOSGA_ROOT.'/src/Novosga/Model');
            $config = Setup::createAnnotationMetadataConfiguration($paths, $this->isDev);
            $config->setAutoGenerateProxyClasses($this->isDev ? 1 : 2);
            $config->setProxyDir(NOVOSGA_CACHE);
            $config->setProxyNamespace('Novosga\Proxies');
            // caching
            $this->cacheDriver = $this->createDoctrineCache($this->isDev);
            $config->setMetadataCacheImpl($this->cacheDriver);
            $config->setQueryCacheImpl($this->cacheDriver);
            $config->setResultCacheImpl($this->cacheDriver);

            // custom config
            $customSetup = $this->get('setup');
            if ($customSetup && is_callable($customSetup)) {
                $customSetup($config);
            }

            $this->em = EntityManager::create($this->values(), $config);
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
    private function createDoctrineCache($isDev)
    {
        if (!$isDev) {
            // APC
            if (extension_loaded('apc') && ini_get('apc.enabled')) {
                return new ApcCache();
            }
            // XCache
            elseif (extension_loaded('xcache')) {
                return new XcacheCache();
            }
        }

        return new ArrayCache();
    }
}
