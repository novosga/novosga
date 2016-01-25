<?php

namespace Novosga\UsersBundle;

class NovosgaUsersBundle extends \Novosga\ModuleBundle
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
