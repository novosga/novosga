<?php

declare(strict_types=1);

namespace App\NovosgaChamadaSenhaBundle;

use Novosga\Module\BaseModule;

class NovosgaChamadaSenhaBundle extends BaseModule
{
    public function getKeyName(): string
    {
        return 'novosga.chamadasenha';
    }

    public function getIconName(): string
    {
        return 'bullhorn';
    }

    public function getDisplayName(): string
    {
        return 'Chamada de Senha';
    }

    public function getHomeRoute(): string
    {
        return 'novosga_chamadasenha_index';
    }
}
