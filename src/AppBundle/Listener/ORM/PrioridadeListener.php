<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Listener\ORM;

use Exception;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Novosga\Entity\Prioridade;
use Novosga\Entity\Atendimento;

/**
 * PrioridadeListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class PrioridadeListener
{
    
    public function preRemove(Prioridade $prioridade, LifecycleEventArgs $args)
    {
        if ($prioridade->getId() === 1) {
            throw new Exception('Não pode remover a prioridade inicial');
        }
        
        $em = $args->getEntityManager();
        
        $total = (int) $em->createQueryBuilder()
            ->select('COUNT(e)')
            ->from(Atendimento::class, 'e')
            ->where('e.prioridade = :prioridade')
            ->setParameter('prioridade', $prioridade)
            ->getQuery()
            ->getSingleScalarResult();
        
        if ($total > 0) {
            throw new Exception('Não é possível remover a prioridade porque já existe atendimento vinculado.');
        }
    }
}
