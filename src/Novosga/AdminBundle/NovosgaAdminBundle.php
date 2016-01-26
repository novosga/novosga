<?php

namespace Novosga\AdminBundle;

use Novosga\ModuleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NovosgaAdminBundle extends Bundle implements ModuleInterface
{
    public function getDisplayName() 
    {
        return 'Administração';
    }

    public function getHomeRoute() 
    {
        return 'novosga_admin_index';
    }
}
