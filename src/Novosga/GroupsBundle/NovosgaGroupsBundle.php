<?php

namespace Novosga\GroupsBundle;

class NovosgaGroupsBundle extends \Novosga\ModuleBundle
{
    public function getDisplayName() 
    {
        return 'Grupos';
    }
    
    public function getHomeRoute() 
    {
        return 'novosga_groups_index';
    }
}
