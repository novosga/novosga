<?php

namespace Novosga\UsersBundle;

use Novosga\ModuleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NovosgaUsersBundle extends Bundle implements ModuleInterface
{
    public function getDisplayName() 
    {
        return 'Usuários';
    }
    
    public function getHomeRoute() 
    {
        return 'novosga_users_index';
    }
}
