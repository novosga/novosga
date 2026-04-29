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
use App\Entity\Cliente;
use Novosga\Entity\ClienteInterface;
use Novosga\Repository\ClienteRepositoryInterface;

/**
 * @extends ServiceEntityRepository<ClienteInterface>
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class ClienteRepository extends ServiceEntityRepository implements ClienteRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cliente::class);
    }

    private const PHONE_FIELDS = ['telefone'];

    /** @return ClienteInterface[] */
    public function findBy(
        array $criteria,
        array|null $orderBy = null,
        int|null $limit = null,
        int|null $offset = null,
    ): array {
        $phoneFields = array_intersect_key($criteria, array_flip(self::PHONE_FIELDS));
        $regularFields = array_diff_key($criteria, $phoneFields);

        if (empty($phoneFields)) {
            return parent::findBy($criteria, $orderBy, $limit, $offset);
        }

        $qb = $this->createQueryBuilder('e');

        foreach ($regularFields as $field => $value) {
            $qb
                ->andWhere("e.{$field} = :{$field}")
                ->setParameter($field, $value);
        }

        foreach ($phoneFields as $field => $value) {
            $qb
                ->andWhere("REGEX_REPLACE(e.{$field}, '[^0-9]', '') = REGEX_REPLACE(:{$field}, '[^0-9]', '')")
                ->setParameter($field, $value);
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

    /**
     * Retorna todos os clientes ordenados pelo nome
     * @return ClienteInterface[]
     */
    public function findAll(): array
    {
        return $this->findBy([], ['nome' => 'ASC']);
    }

    /** @return ClienteInterface[] */
    public function findByDocumento(string $documento): array
    {
        return $this
            ->createQueryBuilder('e')
            ->where('e.documento LIKE :documento')
            ->setParameter('documento', $documento)
            ->getQuery()
            ->getResult();
    }
}
