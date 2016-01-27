<?php

namespace Novosga\ServicesBundle;

use Novosga\ModuleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NovosgaServicesBundle extends Bundle implements ModuleInterface
{
    public function getDisplayName() 
    {
        return 'Serviços';
    }

    public function getHomeRoute() 
    {
        return 'novosga_services_index';
    }
}
