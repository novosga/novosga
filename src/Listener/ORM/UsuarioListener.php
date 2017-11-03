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

use Exception;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Novosga\Entity\Usuario;

/**
 * UsuarioListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class UsuarioListener
{
    public function prePersist(Usuario $usuario, LifecycleEventArgs $args)
    {
        $usuario->setCreatedAt(new \DateTime);
    }
    
    public function preUpdate(Usuario $usuario, LifecycleEventArgs $args)
    {
        $usuario->setUpdatedAt(new \DateTime);
    }
    
    public function preRemove(Usuario $usuario, LifecycleEventArgs $args)
    {
        if ($usuario->isAdmin()) {
            throw new Exception('Não pode remover um usuário administrador.');
        }
    }
}
