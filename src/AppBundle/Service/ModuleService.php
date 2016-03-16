<?php

namespace AppBundle\Service;

class ModuleService
{
    
    public function getModules()
    {
        $filename = realpath(__DIR__ . '/../../../var/config/modules.php');
        
        return require $filename;
    }
    
    public function discover()
    {
        $searchPath = realpath(__DIR__ . '/../../../modules');
        $finder     = new Symfony\Component\Finder\Finder();
        $finder->files()
               ->in($searchPath)
               ->name('*Bundle.php');
        
        $modules = [];

        foreach ($finder as $file) {
            $path       = substr($file->getRealpath(), strlen($searchPath) + 1, -4);
            $parts      = explode('/', $path);
            $class      = array_pop($parts);
            $namespace  = implode('\\', $parts);
            $class      = '\\' . $namespace.'\\'.$class;
            if (class_exists($class)) {
                $modules[]  = new $class();
            }
        }
        
        return $modules;
    }
    
}