<?php

namespace Novosga\MonitorBundle;

use Novosga\ModuleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NovosgaMonitorBundle extends Bundle implements ModuleInterface
{
    public function getIconName()
    {
        return 'desktop';
    }

    public function getDisplayName()
    {
        return 'Monitor';
    }

    public function getHomeRoute()
    {
        return 'novosga_monitor_index';
    }
}
