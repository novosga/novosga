<?php

namespace AppBundle\Config;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * NovosgaConfiguration
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class NovosgaConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('novosga');

        $rootNode
            ->children()
                ->arrayNode('modules')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('class')->end()
                            ->scalarNode('active')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('plugins')
                    ->children()
                        ->scalarNode('dir')->end()
                        ->scalarNode('active')->end()
                    ->end()
                ->end()
        ;

        return $treeBuilder;
    }
}
