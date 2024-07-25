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
use App\Entity\Servico;
use App\Entity\ServicoUnidade;
use App\Entity\ServicoUsuario;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreRemoveEventArgs;

/**
 * ServicoListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[AsEntityListener]
class ServicoListener
{
    public function preRemove(Servico $servico, PreRemoveEventArgs $args): void
    {
        /** @var EntityManagerInterface */
        $em = $args->getObjectManager();

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

        $total = (int) $em
            ->createQueryBuilder()
            ->select('COUNT(1)')
            ->from(Servico::class, 'e')
            ->where('e.mestre = :servico')
            ->andWhere('e.deletedAt IS NULL')
            ->setParameter('servico', $servico)
            ->getQuery()
            ->getSingleScalarResult();

        if ($total > 0) {
            throw new Exception('Não é possível remover o serviço porque possui subserviços vinculados.');
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
