<?php

namespace AppBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @param boolean $debug
     */
    public function __construct()
    {
    }

    public function getConfigTreeBuilder()
    {
        $tb = new TreeBuilder();
        
        $root = $tb
            ->root('novosga', 'array')
                ->children()
            ;
        
        $this->addTicketSection($root);
        $this->addModulesSection($root);
        $this->addPluginsSection($root);

        return $tb;
    }

    private function addTicketSection(NodeBuilder $builder)
    {
        $builder
            ->arrayNode('ticket')
                ->children()
                    ->arrayNode('statuses')
                        ->requiresAtLeastOneElement()
                        ->prototype('scalar')->end()
                    ->end()
                
                    ->arrayNode('workflow')
                        ->requiresAtLeastOneElement()
                        ->useAttributeAsKey('name')
                        ->prototype('variable')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
    
    private function addModulesSection(NodeBuilder $builder)
    {
        $builder
            ->arrayNode('modules')
                ->prototype('array')
                    ->children()
                        ->scalarNode('class')->end()
                        ->scalarNode('active')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
    
    private function addPluginsSection(NodeBuilder $builder)
    {
        $builder
            ->arrayNode('plugins')
                ->prototype('array')
                    ->children()
                        ->scalarNode('dir')->end()
                        ->scalarNode('active')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
