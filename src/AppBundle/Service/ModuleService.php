<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Service;

use Symfony\Component\Finder\Finder;

class ModuleService
{
 
    const ROOT_DIR = __DIR__ . '/../../..';
    const MODULES_DIR = self::ROOT_DIR . '/modules';
    
    /**
     * @var string
     */
    private $modulesCache;
    
    public function __construct()
    {
        $this->modulesCache = self::ROOT_DIR . '/var/modules.php.cache';
    }

    /**
     * @return array
     */
    public function getModules()
    {
        $modules = null;
        
        if (file_exists($this->modulesCache)) {
            $modules = require $this->modulesCache;
        }
        
        if (!is_array($modules)) {
            $modules = $this->discover();
            $this->createCache($modules);
        }

        return $modules;
    }

    /**
     * @return Generator
     */
    public function getActiveModules()
    {
        $modules = $this->getModules();
        
        foreach ($modules as $entry) {
            if (isset($entry['active']) && $entry['active']) {
                $actives[] = $entry;
                yield $entry;
            }
        }
    }

    /**
     *
     * @param string
     * @param bool
     */
    public function update(string $key, bool $active)
    {
        $modules = $this->getModules();
        
        if (isset($modules[$key])) {
            $modules[$key]['active'] = $active;
            $this->createCache($modules);
            
            return true;
        }

        return false;
    }

    public function discover()
    {
        $searchPath = realpath(self::MODULES_DIR);
        
        $finder     = new Finder();
        $finder->files()
               ->in($searchPath)
               ->name('*Bundle.php');
        
        $modules = [];

        foreach ($finder as $file) {
            $class = $this->extractClassName($file->getRealpath(), $searchPath);
            
            if (count($class) === 2 && class_exists($class[1])) {
                $key = $this->classNameToKey($class[0]);

                $modules[$key]  = [
                    'active' => true,
                    'class' => $class[1]
                ];
            }
        }

        return $modules;
    }
    
    private function createCache(array $modules)
    {
        $str = \Novosga\Util\Arrays::toString($modules);
        $time = time();
        file_put_contents($this->modulesCache, "<?php /* generated at {$time} */ return {$str};");
    }
    
    private function extractClassName($filename, $basepath)
    {
        $path       = substr($filename, strlen($basepath) + 1, -4);
        $parts      = explode('/', $path);
        $class      = array_pop($parts);

        $content = file_get_contents($filename);
        preg_match("/namespace\ (.*)?;/", $content, $match);

        if (count($match) !== 2) {
            return null;
        }
        
        $namespace = $match[1];
        
        return [
            $class,
            $namespace . '\\' . $class,
        ];
    }
    
    private function classNameToKey($class)
    {
        $classWithoutSuffix = substr($class, 0, strpos($class, 'Bundle'));
        $tokens = preg_split('/(?=[A-Z])/', $classWithoutSuffix, -1, PREG_SPLIT_NO_EMPTY);
        $key = strtolower(implode('.', $tokens));
        
        return $key;
    }
}
