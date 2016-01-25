<?php

namespace Novosga\RolesBundle;

class NovosgaRolesBundle extends \Novosga\ModuleBundle
{
    public function getDisplayName() 
    {
        return 'Cargos';
    }
    
    public function getHomeRoute() 
    {
        return 'novosga_roles_index';
    }
}
