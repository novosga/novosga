<?php

namespace Novosga\ReportsBundle;

use Novosga\Module\BaseModule;

class NovosgaReportsBundle extends BaseModule
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
