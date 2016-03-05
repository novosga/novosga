<?php

namespace Novosga\RolesBundle;

use Novosga\Module\BaseModule;

class NovosgaRolesBundle extends BaseModule
{
    public function getIconName()
    {
        return 'briefcase';
    }

    public function getDisplayName()
    {
        return 'Cargos';
    }

    public function getHomeRoute()
    {
        return 'novosga_roles_index';
    }
}
