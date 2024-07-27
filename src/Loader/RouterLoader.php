<?php

declare(strict_types=1);

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Loader;

use Novosga\Module\ModuleInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouterLoader
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[AutoconfigureTag('routing.loader')]
class RouterLoader extends Loader
{
    private bool $loaded = false;

    public function __construct(
        private readonly KernelInterface $kernel,
    ) {
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return 'novosga.modules' === $type;
    }

    public function load(mixed $resource, ?string $type = null): mixed
    {
        if ($this->loaded) {
            throw new \RuntimeException('Do not add this loader twice');
        }

        $routes = $this->createRoutesCollection();

        $this->loaded = true;

        return $routes;
    }

    private function createRoutesCollection(): RouteCollection
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

    protected function getModuleRouteCollection(ModuleInterface $bundle): RouteCollection
    {
        $type = 'attribute';
        $resource = "@{$bundle->getName()}/Controller/";

        $routes = $this->import($resource, $type);
        $routes->addPrefix("/{$bundle->getKeyName()}");

        return $routes;
    }
}
