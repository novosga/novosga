<?php

namespace Novosga\ReportsBundle;

use Novosga\ModuleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NovosgaReportsBundle extends Bundle implements ModuleInterface
{
    public function getDisplayName() 
    {
        return 'Relatórios';
    }

    public function getHomeRoute() 
    {
        return 'novosga_reports_index';
    }
}
