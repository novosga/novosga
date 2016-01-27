<?php

namespace Novosga\ReportsBundle;

use Novosga\ModuleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NovosgaReportsBundle extends Bundle implements ModuleInterface
{
    public function getIconName()
    {
        return 'bar-chart';
    }

    public function getDisplayName()
    {
        return 'Relatórios';
    }

    public function getHomeRoute()
    {
        return 'novosga_reports_index';
    }
}
