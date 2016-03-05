<?php

namespace Novosga\GroupsBundle;

use Novosga\Module\BaseModule;

class NovosgaGroupsBundle extends BaseModule
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
