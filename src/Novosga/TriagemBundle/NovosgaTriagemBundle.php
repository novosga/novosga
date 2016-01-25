<?php

namespace Novosga\TriagemBundle;

class NovosgaTriagemBundle extends \Novosga\ModuleBundle
{
    public function getDisplayName() 
    {
        return 'Triagem';
    }

    public function getHomeRoute() 
    {
        return 'novosga_triagem_index';
    }
}
