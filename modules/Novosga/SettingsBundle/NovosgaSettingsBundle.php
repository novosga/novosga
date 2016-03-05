<?php

namespace Novosga\SettingsBundle;

use Novosga\Module\BaseModule;

class NovosgaSettingsBundle extends BaseModule
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
