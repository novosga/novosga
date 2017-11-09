<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Listener\ORM;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Novosga\Entity\Perfil;

/**
 * PerfilListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class PerfilListener
{
    public function prePersist(Perfil $perfil, LifecycleEventArgs $args)
    {
        $perfil->setCreatedAt(new \DateTime);
    }
    
    public function preUpdate(Perfil $perfil, LifecycleEventArgs $args)
    {
        $perfil->setUpdatedAt(new \DateTime);
    }
}
