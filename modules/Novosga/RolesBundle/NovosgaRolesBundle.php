<?php

namespace Novosga\RolesBundle;

use Novosga\ModuleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NovosgaRolesBundle extends Bundle implements ModuleInterface
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
