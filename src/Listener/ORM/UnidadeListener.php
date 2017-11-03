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
use Novosga\Entity\Unidade;
use Novosga\Entity\ServicoUnidade;

/**
 * UnidadeListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class UnidadeListener
{
    public function prePersist(Unidade $unidade, LifecycleEventArgs $args)
    {
        $unidade->setCreatedAt(new \DateTime);
    }
    
    public function preUpdate(Unidade $unidade, LifecycleEventArgs $args)
    {
        $unidade->setUpdatedAt(new \DateTime);
    }
    
    public function preRemove(Unidade $unidade, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        
        $total = (int) $em
                ->createQueryBuilder()
                ->select('COUNT(1)')
                ->from(ServicoUnidade::class, 'e')
                ->where('e.unidade = :unidade')
                ->andWhere('e.ativo = TRUE')
                ->setParameter('unidade', $unidade)
                ->getQuery()
                ->getSingleScalarResult();
        
        if ($total > 0) {
            throw new Exception('Não é possível remover a unidade porque possui serviços habilitados.');
        }
        
        $em
            ->createQueryBuilder()
            ->delete(ServicoUnidade::class, 'e')
            ->where('e.unidade = :unidade')
            ->setParameter('unidade', $unidade)
            ->getQuery()
            ->execute();
    }
}
