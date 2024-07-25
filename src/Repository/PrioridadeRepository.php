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

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Prioridade;
use Novosga\Entity\PrioridadeInterface;
use Novosga\Repository\PrioridadeRepositoryInterface;

/**
 * @extends ServiceEntityRepository<PrioridadeInterface>
 *
 * @method Prioridade|null find($id, $lockMode = null, $lockVersion = null)
 * @method Prioridade|null findOneBy(array $criteria, array $orderBy = null)
 * @method Prioridade[]    findAll()
 * @method Prioridade[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class PrioridadeRepository extends ServiceEntityRepository implements PrioridadeRepositoryInterface
{
    /** @use SoftDeleteTrait<Prioridade> */
    use SoftDeleteTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Prioridade::class);
    }

    /** @return PrioridadeInterface[] */
    public function findAtivas(): array
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
