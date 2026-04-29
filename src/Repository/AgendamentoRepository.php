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
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class AgendamentoRepository extends ServiceEntityRepository implements AgendamentoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agendamento::class);
    }

    private const LIKE_FIELDS = ['cliente.nome'];
    private const IN_FIELDS = ['servico.id'];
    private const PHONE_FIELDS = ['cliente.telefone'];

    /** @return AgendamentoInterface[] */
    public function findBy(
        array $criteria,
        array|null $orderBy = null,
        int|null $limit = null,
        int|null $offset = null,
    ): array {
        $qb = $this->createQueryBuilder('e');
        $joins = [];

        foreach ($criteria as $field => $value) {
            $param = str_replace('.', '_', $field);

            if (str_contains($field, '.')) {
                [$relation, $column] = explode('.', $field, 2);
                if (!isset($joins[$relation])) {
                    $qb->join("e.{$relation}", $relation);
                    $joins[$relation] = true;
                }

                if (in_array($field, self::LIKE_FIELDS, true)) {
                    $qb
                        ->andWhere("{$relation}.{$column} LIKE :{$param}")
                        ->setParameter($param, $value);
                } elseif (in_array($field, self::IN_FIELDS, true)) {
                    $ids = is_array($value) ? $value : array_map('trim', explode(',', (string) $value));
                    $qb
                        ->andWhere("{$relation}.{$column} IN (:{$param})")
                        ->setParameter($param, $ids);
                } elseif (in_array($field, self::PHONE_FIELDS, true)) {
                    $qb
                        ->andWhere(
                            "REGEX_REPLACE({$relation}.{$column},
                            '[^0-9]', '') = REGEX_REPLACE(:{$param},
                            '[^0-9]', '')",
                        )
                        ->setParameter($param, $value);
                } else {
                    $qb
                        ->andWhere("{$relation}.{$column} = :{$param}")
                        ->setParameter($param, $value);
                }
            } else {
                $qb
                    ->andWhere("e.{$field} = :{$param}")
                    ->setParameter($param, $value);
            }
        }

        foreach ($orderBy ?? [] as $field => $direction) {
            $qb->addOrderBy("e.{$field}", $direction);
        }

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        if ($offset !== null) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
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
