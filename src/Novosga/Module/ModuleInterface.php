<?php

namespace Novosga\Module;

/**
 * ModuleInterface
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
interface ModuleInterface
{
    public function getKeyName();
    
    public function getRoleName();
    
    public function getIconName();

    public function getDisplayName();

    public function getHomeRoute();
}
