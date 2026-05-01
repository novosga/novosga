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
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
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
    private const LIKE_FIELDS = ['cliente.nome'];
    private const IN_FIELDS = ['servico.id'];
    private const DIGITS_FIELDS = ['cliente.telefone', 'cliente.documento'];
    private const DATE_FIELDS = ['data', 'cliente.dataNascimento'];
    private const TIME_FIELDS = ['hora'];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agendamento::class);
    }

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
                [$relation, $property] = explode('.', $field, 2);
                if (!isset($joins[$relation])) {
                    $qb->join("e.{$relation}", $relation);
                    $joins[$relation] = true;
                }
                $path = "{$relation}.{$property}";
            } else {
                $path = "e.{$field}";
            }
            if (in_array($field, self::LIKE_FIELDS, true)) {
                $qb
                    ->andWhere("{$path} LIKE :{$param}")
                    ->setParameter($param, $value);
            } elseif (in_array($field, self::IN_FIELDS, true)) {
                $ids = is_array($value) ? $value : array_map('trim', explode(',', (string) $value));
                $qb
                    ->andWhere("{$path} IN (:{$param})")
                    ->setParameter($param, $ids);
            } elseif (in_array($field, self::DIGITS_FIELDS, true)) {
                $qb
                    ->andWhere(
                        "REGEX_REPLACE({$path},
                        '[^0-9]', '') = REGEX_REPLACE(:{$param},
                        '[^0-9]', '')",
                    )
                    ->setParameter($param, $value);
            } elseif (in_array($field, self::DATE_FIELDS, true)) {
                $normalized = $this->normalizeDateValue((string) $value);
                $qb
                    ->andWhere("{$path} = :{$param}")
                    ->setParameter($param, $normalized);
            } elseif (in_array($field, self::TIME_FIELDS, true)) {
                $normalized = $this->normalizeTimeValue((string) $value);
                $qb
                    ->andWhere("{$path} = :{$param}")
                    ->setParameter($param, $normalized);
            } else {
                $qb
                    ->andWhere("{$path} = :{$param}")
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

    private function normalizeDateValue(string $value): string
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if ($date === false) {
            throw new InvalidArgumentException("Invalid date value: {$value}");
        }
        return $value;
    }

    private function normalizeTimeValue(string $value): string
    {
        $time = DateTimeImmutable::createFromFormat('H:i', $value);
        if ($time === false) {
            throw new InvalidArgumentException("Invalid time value: {$time}");
        }
        return $value;
    }
}
