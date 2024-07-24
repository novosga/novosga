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
use App\Entity\Prioridade;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreRemoveEventArgs;

/**
 * PrioridadeListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[AsEntityListener]
class PrioridadeListener
{
    public function preRemove(Prioridade $prioridade, PreRemoveEventArgs $args): void
    {
        if ($prioridade->getId() === 1) {
            throw new Exception('Não pode remover a prioridade inicial');
        }
    }
}
