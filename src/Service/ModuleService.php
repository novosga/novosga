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

namespace App\Service;

use Novosga\Service\ModuleServiceInterface;
use Novosga\Module\ModuleInterface;
use Novosga\Settings\InstalledModule;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ModuleService
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class ModuleService implements ModuleServiceInterface
{
    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function getInstalledModules(): array
    {
        $modules = array_map(function ($module) {
            $name = $this->translator->trans($module->getDisplayName(), [], $module->getName());
            return new InstalledModule(
                active: true,
                displayName: $name,
                key: $module->getKeyName(),
                iconName: $module->getIconName(),
                homeRoute: $module->getHomeRoute(),
            );
        }, $this->filterModules($this->kernel->getBundles()));

        usort(
            $modules,
            fn (InstalledModule $a, InstalledModule $b) => strcmp($a->displayName, $b->displayName),
        );

        return $modules;
    }

    /**
     * @param BundleInterface[] $bundles
     * @return ModuleInterface[]
     */
    private function filterModules(array $bundles): array
    {
        $modules = [];

        foreach ($bundles as $bundle) {
            if ($bundle instanceof ModuleInterface) {
                $modules[] = $bundle;
            }
        }

        return $modules;
    }
}
