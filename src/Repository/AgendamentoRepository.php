<?php

declare(strict_types=1);

/*
 * This file is part of the NovoSGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Agendamento;
use DateTimeInterface;
use Novosga\Entity\AgendamentoInterface;
use Novosga\Entity\ServicoInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Repository\AgendamentoRepositoryInterface;

/**
 * @extends ServiceEntityRepository<AgendamentoInterface>
 *
 * @method Agendamento|null find($id, $lockMode = null, $lockVersion = null)
 * @method Agendamento|null findOneBy(array $criteria, array $orderBy = null)
 * @method Agendamento[]    findAll()
 * @method Agendamento[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class AgendamentoRepository extends ServiceEntityRepository implements AgendamentoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agendamento::class);
    }

    public function findByUnidadeAndServicoAndData(
        UnidadeInterface|int $unidade,
        ServicoInterface|int $servico,
        DateTimeInterface $data,
    ): array {
        return $this
            ->createQueryBuilder('e')
            ->andWhere('e.unidade = :unidade')
            ->andWhere('e.servico = :servico')
            ->andWhere('e.data = :data')
            ->setParameter('unidade', $unidade)
            ->setParameter('servico', $servico)
            ->setParameter('data', $data->format('Y-m-d'))
            ->addOrderBy('e.hora', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
