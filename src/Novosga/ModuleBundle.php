<?php

namespace Novosga;

use Symfony\Component\HttpKernel\Bundle\Bundle;

abstract class ModuleBundle extends Bundle
{
    
    abstract public function getDisplayName();
    
    abstract public function getHomeRoute();
    
}