<?php

namespace Novosga\UsersBundle;

use Novosga\Module\BaseModule;

class NovosgaUsersBundle extends BaseModule
{
    public function getIconName()
    {
        return 'users';
    }

    public function getDisplayName()
    {
        return 'Usuários';
    }

    public function getHomeRoute()
    {
        return 'novosga_users_index';
    }
}
