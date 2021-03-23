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

use Novosga\Module\ModuleInterface;

class ModuleService
{
    /**
     * @return array
     */
    public function filterModules(array $bundles): array
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
