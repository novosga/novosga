<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Loader;

use RuntimeException;
use Novosga\Module\ModuleInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouterLoader
 */
class RouterLoader extends Loader
{
    /**
     * @var bool
     */
    private $loaded = false;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Loads a resource.
     *
     * @param mixed  $resource The resource
     * @param string $type     The resource type
     *
     * @return RouteCollection
     *
     * @throws RuntimeException Loader is added twice
     */
    public function load($resource, $type = null)
    {
        if ($this->loaded) {
            throw new \RuntimeException('Do not add this loader twice');
        }

        $routes = $this->createRoutesCollection();
        
        $this->loaded = true;

        return $routes;
    }

    /**
     * Return route collection for injected plugins
     *
     * @return RouteCollection Collection generated
     */
    protected function createRoutesCollection()
    {
        $routes = new RouteCollection();
        
        foreach ($this->kernel->getBundles() as $bundle) {
            if ($bundle instanceof ModuleInterface) {
                $routes->addCollection(
                    $this->getModuleRouteCollection($bundle->getKeyName(), $bundle->getName(), $bundle->getPath())
                );
            }
        }

        return $routes;
    }

    /**
     * @return RouteCollection
     */
    protected function getModuleRouteCollection($keyName, $name, $path)
    {
        $routingFilePath = '/Resources/config/routing.yml';
        $resourcePath = $path . $routingFilePath;

        // check yaml
        if (file_exists($resourcePath)) {
            $type = 'yaml';
            $resource = "@{$name}{$routingFilePath}";
        } else {
            // annotation
            $type = 'annotation';
            $resource = "@{$name}/Controller/";
        }
        
        $routes = $this->import($resource, $type);
        $routes->addPrefix("/{$keyName}");
        
        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return 'novosga.modules' === $type;
    }
}
