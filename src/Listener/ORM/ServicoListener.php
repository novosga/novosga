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
use Novosga\Entity\Servico;
use Novosga\Entity\ServicoUnidade;
use Novosga\Entity\ServicoUsuario;

/**
 * ServicoListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class ServicoListener
{
    public function prePersist(Servico $servico, LifecycleEventArgs $args)
    {
        $servico->setCreatedAt(new \DateTime);
    }
    
    public function preUpdate(Servico $servico, LifecycleEventArgs $args)
    {
        $servico->setUpdatedAt(new \DateTime);
    }
    
    public function preRemove(Servico $servico, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        
        $total = (int) $em
                ->createQueryBuilder()
                ->select('COUNT(1)')
                ->from(ServicoUnidade::class, 'e')
                ->where('e.servico = :servico')
                ->andWhere('e.ativo = TRUE')
                ->setParameter('servico', $servico)
                ->getQuery()
                ->getSingleScalarResult();
        
        if ($total > 0) {
            throw new Exception('Não é possível remover o serviço porque está habilitado em uma unidade.');
        }
        
        $em
            ->createQueryBuilder()
            ->delete(ServicoUnidade::class, 'e')
            ->where('e.servico = :servico')
            ->setParameter('servico', $servico)
            ->getQuery()
            ->execute();
        
        $em
            ->createQueryBuilder()
            ->delete(ServicoUsuario::class, 'e')
            ->where('e.servico = :servico')
            ->setParameter('servico', $servico)
            ->getQuery()
            ->execute();
    }
}
