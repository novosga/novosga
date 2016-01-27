<?php

namespace Novosga\SettingsBundle;

use Novosga\ModuleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NovosgaSettingsBundle extends Bundle implements ModuleInterface
{
    public function getIconName()
    {
        return 'wrench';
    }

    public function getDisplayName()
    {
        return 'Configurações';
    }

    public function getHomeRoute()
    {
        return 'novosga_settings_index';
    }
}
