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
use Novosga\Entity\ServicoUnidade;
use Novosga\Repository\ServicoUnidadeRepositoryInterface;

/**
 * ServicoUnidadeRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class ServicoUnidadeRepository extends ServiceEntityRepository implements ServicoUnidadeRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServicoUnidade::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getAll($unidade)
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('e')
            ->from(ServicoUnidade::class, 'e')
            ->join('e.servico', 's')
            ->where('e.unidade = :unidade')
            ->andWhere('s.deletedAt IS NULL')
            ->setParameters([
                'unidade' => $unidade,
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function get($unidade, $servico)
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
            ->setParameters([
                'servico' => $servico,
                'unidade' => $unidade,
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }
}
