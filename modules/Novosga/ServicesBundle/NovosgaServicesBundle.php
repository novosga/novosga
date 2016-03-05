<?php

namespace Novosga\ServicesBundle;

use Novosga\Module\BaseModule;

class NovosgaServicesBundle extends BaseModule
{
    public function getIconName()
    {
        return 'file-text-o';
    }

    public function getDisplayName()
    {
        return 'Serviços';
    }

    public function getHomeRoute()
    {
        return 'novosga_services_index';
    }
}
