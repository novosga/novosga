<?php

namespace AppBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppBundle extends Bundle
{   
    
    public function build(ContainerBuilder $container) 
    {
        parent::build($container);

        $modelDir = realpath(__DIR__.'/Resources/config/doctrine/');
        $mappings = [
            $modelDir => 'Novosga\Entity',
        ];

        if ($this->isOrmEnabled()) {
            $container->addCompilerPass(
                DoctrineOrmMappingsPass::createYamlMappingDriver($mappings)
            );
        }
    }

    private function isOrmEnabled()
    {
        $ormCompilerClass = 'Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass';
        return class_exists($ormCompilerClass);
    }
    
    public function registerModules(array &$bundles)
    {
        $service = new Service\ModuleService();
        $modules = $service->getActiveModules();
        
        foreach ($modules as $entry) {
            $module = new $entry['class'];
            if ($module instanceof \Novosga\Module\ModuleInterface) {
                $bundles[] = $module;
            }
        }
    }
}
