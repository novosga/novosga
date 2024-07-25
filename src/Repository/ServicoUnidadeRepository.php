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
use App\Entity\ServicoUnidade;
use Novosga\Entity\ServicoInterface;
use Novosga\Entity\ServicoUnidadeInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Repository\ServicoUnidadeRepositoryInterface;

/**
 * @extends ServiceEntityRepository<ServicoUnidadeInterface>
 *
 * @method ServicoUnidade|null find($id, $lockMode = null, $lockVersion = null)
 * @method ServicoUnidade|null findOneBy(array $criteria, array $orderBy = null)
 * @method ServicoUnidade[]    findAll()
 * @method ServicoUnidade[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class ServicoUnidadeRepository extends ServiceEntityRepository implements ServicoUnidadeRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServicoUnidade::class);
    }

    /** {@inheritdoc} */
    public function getAll(UnidadeInterface|int $unidade): array
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('e')
            ->from(ServicoUnidade::class, 'e')
            ->join('e.servico', 's')
            ->where('e.unidade = :unidade')
            ->andWhere('s.deletedAt IS NULL')
            ->setParameter('unidade', $unidade)
            ->getQuery()
            ->getResult();
    }

    /** {@inheritdoc} */
    public function get(UnidadeInterface|int $unidade, ServicoInterface|int $servico): ?ServicoUnidadeInterface
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('e')
            ->from(ServicoUnidade::class, 'e')
            ->join('e.servico', 's')
            ->where('e.unidade = :unidade')
            ->andWhere('s = :servico')
            ->andWhere('s.deletedAt IS NULL')
            ->setParameter('servico', $servico)
            ->setParameter('unidade', $unidade)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
