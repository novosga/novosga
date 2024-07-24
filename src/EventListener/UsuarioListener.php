<?php

declare(strict_types=1);

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventListener;

use Exception;
use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreRemoveEventArgs;

/**
 * UsuarioListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[AsEntityListener]
class UsuarioListener
{
    public function preRemove(Usuario $usuario, PreRemoveEventArgs $args): void
    {
        if ($usuario->isAdmin()) {
            throw new Exception('Não pode remover um usuário administrador.');
        }
    }
}
