<?php

namespace Novosga\GroupsBundle;

use Novosga\ModuleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NovosgaGroupsBundle extends Bundle implements ModuleInterface
{
    public function getIconName()
    {
        return 'object-group';
    }

    public function getDisplayName()
    {
        return 'Grupos';
    }

    public function getHomeRoute()
    {
        return 'novosga_groups_index';
    }
}
