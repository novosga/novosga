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
use App\Entity\Local;
use App\Entity\ServicoUnidade;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreRemoveEventArgs;

/**
 * LocalListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[AsEntityListener]
class LocalListener
{
    public function preRemove(Local $local, PreRemoveEventArgs $args): void
    {
        /** @var EntityManagerInterface */
        $em = $args->getObjectManager();
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
