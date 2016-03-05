<?php

namespace Novosga\TriagemBundle;

use Novosga\Module\BaseModule;

class NovosgaTriagemBundle extends BaseModule
{
    public function getIconName()
    {
        return 'print';
    }

    public function getDisplayName()
    {
        return 'Triagem';
    }

    public function getHomeRoute()
    {
        return 'novosga_triagem_index';
    }
}
