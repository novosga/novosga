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
use App\Entity\Atendimento;
use Novosga\Entity\AtendimentoInterface;
use Novosga\Entity\ServicoInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Repository\AtendimentoRepositoryInterface;

/**
 * @extends ServiceEntityRepository<AtendimentoInterface>
 *
 * @method Atendimento|null find($id, $lockMode = null, $lockVersion = null)
 * @method Atendimento|null findOneBy(array $criteria, array $orderBy = null)
 * @method Atendimento[]    findAll()
 * @method Atendimento[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class AtendimentoRepository extends ServiceEntityRepository implements AtendimentoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Atendimento::class);
    }

    /** {@inheritdoc} */
    public function countByServicos(UnidadeInterface $unidade, array $servicos, ?string $status = null): array
    {
        $qb = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('s.id, COUNT(e) as total')
            ->from(Atendimento::class, 'e')
            ->join('e.servico', 's')
            ->where('e.unidade = :unidade')
            ->groupBy('s.id')
            ->setParameter('unidade', $unidade);

        if (count($servicos)) {
            $qb
                ->andWhere('e.servico IN (:servicos)')
                ->setParameter('servicos', $servicos);
        }

        if ($status) {
            $qb
                ->andWhere('e.status = :status')
                ->setParameter('status', $status);
        }

        $rs = $qb
            ->getQuery()
            ->getArrayResult();

        return $rs;
    }

    /** {@inheritdoc} */
    public function getUltimo(UnidadeInterface $unidade, ServicoInterface $servico = null): ?AtendimentoInterface
    {
        $qb = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('e')
            ->from(Atendimento::class, 'e')
            ->where('e.unidade = :unidade')
            ->orderBy('e.id', 'DESC')
            ->setParameter('unidade', $unidade->getId());

        if ($servico) {
            $qb
                ->andWhere('e.servico = :servico')
                ->setParameter('servico', $servico->getId());
        }

        $atendimento = $qb
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        return $atendimento;
    }
}
