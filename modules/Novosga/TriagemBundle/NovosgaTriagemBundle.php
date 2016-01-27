<?php

namespace Novosga\TriagemBundle;

use Novosga\ModuleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NovosgaTriagemBundle extends Bundle implements ModuleInterface
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
