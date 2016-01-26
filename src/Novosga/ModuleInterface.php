<?php

namespace Novosga;

/**
 * ModuleInterface
 * 
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
interface ModuleInterface
{
    public function getDisplayName();
    
    public function getHomeRoute();
}