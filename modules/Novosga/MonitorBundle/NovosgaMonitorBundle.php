<?php

namespace Novosga\MonitorBundle;

use Novosga\Module\BaseModule;

class NovosgaMonitorBundle extends BaseModule
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
