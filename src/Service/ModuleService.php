<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use Symfony\Component\Finder\Finder;

class ModuleService
{
 
    /**
     * @var string
     */
    private $modulesDir;
    
    /**
     * @var string
     */
    private $modulesCache;
    
    public function __construct($rootDir)
    {
        $this->modulesDir   = "{$rootDir}/modules";
        $this->modulesCache = "{$rootDir}/var/modules.php";
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
            $this->dump($this->modulesCache, $modules);
        }

        return $modules;
    }

    /**
     * @return \Generator
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
            $this->dump($this->modulesCache, $modules);
            
            return true;
        }

        return false;
    }

    public function discover()
    {
        $searchPath = realpath($this->modulesDir);
        
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

    private function dump(string $file, array $modules)
    {
        $contents = "<?php\n\nreturn [\n";
        foreach ($modules as $name => $props) {
            $contents .= "    '{$name}' => [";
            foreach ($props as $key => $value) {
                switch ($key) {
                    case 'class':
                        $parsed = "$value::class";
                        break;
                    case 'active':
                        $parsed = $value ? 'true' : 'false';
                        break;
                    default:
                        $parsed = "'{$value}'";
                }
                $contents .= "'$key' => $parsed, ";
            }
            $contents = substr($contents, 0, -2)."],\n";
        }
        $contents .= "];\n";

        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }

        file_put_contents($file, $contents);

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($file);
        }
    }
}
