<?php
declare(strict_types=1);

namespace App\NovosgaMidiaAdminBundle;

use Novosga\Module\BaseModule;

class NovosgaMidiaAdminBundle extends BaseModule
{
    public function getKeyName(): string
    {
        return 'novosga.midiaadmin';
    }

    public function getIconName(): string
    {
        return 'desktop';
    }

    public function getDisplayName(): string
    {
        return 'Mídia Admin';
    }

    public function getHomeRoute(): string
    {
        return 'novosga_midiaadmin_index';
    }
}
