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
use Novosga\Entity\Prioridade;

/**
 * PrioridadeListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class PrioridadeListener
{
    public function prePersist(Prioridade $prioridade, LifecycleEventArgs $args)
    {
        $prioridade->setCreatedAt(new \DateTime);
    }
    
    public function preUpdate(Prioridade $prioridade, LifecycleEventArgs $args)
    {
        $prioridade->setUpdatedAt(new \DateTime);
    }
    
    public function preRemove(Prioridade $prioridade, LifecycleEventArgs $args)
    {
        if ($prioridade->getId() === 1) {
            throw new Exception('Não pode remover a prioridade inicial');
        }
    }
}
