<?php
declare(strict_types=1);

namespace App\NovosgaGestorUnidadeBundle;

use Novosga\Module\BaseModule;

class NovosgaGestorUnidadeBundle extends BaseModule
{
    public function getKeyName(): string
    {
        return 'novosga.gestorunidade';
    }

    public function getIconName(): string
    {
        return 'building';
    }

    public function getDisplayName(): string
    {
        return 'Gestor de Unidade';
    }

    public function getHomeRoute(): string
    {
        return 'novosga_gestorunidade_index';
    }
}
