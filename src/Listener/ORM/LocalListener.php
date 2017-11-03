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
use Novosga\Entity\Local;
use Novosga\Entity\ServicoUnidade;

/**
 * LocalListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class LocalListener
{
    public function prePersist(Local $local, LifecycleEventArgs $args)
    {
        $local->setCreatedAt(new \DateTime);
    }
    
    public function preUpdate(Local $local, LifecycleEventArgs $args)
    {
        $local->setUpdatedAt(new \DateTime);
    }
    
    public function preRemove(Local $local, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        
        $total = (int) $em
                ->createQueryBuilder()
                ->select('COUNT(1)')
                ->from(ServicoUnidade::class, 'e')
                ->where('e.local = :local')
                ->andWhere('e.ativo = TRUE')
                ->setParameter('local', $local)
                ->getQuery()
                ->getSingleScalarResult();
        
        if ($total > 0) {
            throw new Exception('Não é possível remover o local porque possui serviços habilitados.');
        }
        
        $em
            ->createQueryBuilder()
            ->delete(ServicoUnidade::class, 'e')
            ->where('e.local = :local')
            ->setParameter('local', $local)
            ->getQuery()
            ->execute();
    }
}
