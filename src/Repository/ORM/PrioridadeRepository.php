<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository\ORM;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Novosga\Entity\Prioridade;
use Novosga\Repository\PrioridadeRepositoryInterface;

/**
 * PrioridadeRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class PrioridadeRepository extends ServiceEntityRepository implements PrioridadeRepositoryInterface
{
    use SoftDeleteTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Prioridade::class);
    }
    
    public function findAtivas()
    {
        return $this
                ->createQueryBuilder('e')
                ->where('e.deletedAt IS NULL')
                ->andWhere('e.ativo = TRUE')
                ->andWhere('e.peso > 0')
                ->orderBy('e.nome', 'ASC')
                ->getQuery()
                ->getResult();
    }
}
