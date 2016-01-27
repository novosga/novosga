<?php

namespace AppBundle\Loader;

use Novosga\ModuleInterface;
use RuntimeException;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouterLoader
 */
class RouterLoader extends Loader
{
    /**
     * @var boolean
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
                    $this->getModuleRouteCollection($bundle)
                );
            }
        }

        return $routes;
    }

    /**
     * @return RouteCollection
     */
    protected function getModuleRouteCollection(Bundle $bundle)
    {
        $namespace = $bundle->getNamespace();
        $tokens = explode('\\', str_replace('Bundle', '', $namespace));
        $prefix = strtolower(implode('.', $tokens));
        
        $routingFilePath = '/Resources/config/routing.yml';
        $resourcePath = $bundle->getPath() . $routingFilePath;

        // check yaml
        if (file_exists($resourcePath)) {
            $routes = $this->import('@' . $bundle->getName() . $routingFilePath, 'yaml');
        } 
        // annotation
        else {
            $routes = $this->import('@' . $bundle->getName() . '/Controller/', 'annotation');
        }
        
        $routes->addPrefix("/$prefix");

        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return 'novosga.modules' === $type;
    }
}