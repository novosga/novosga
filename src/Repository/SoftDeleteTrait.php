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

/**
 * @template T
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
trait SoftDeleteTrait
{
    /**
     * @param array<string,mixed> $criteria
     * @param array<string,string> $orderBy
     * @return T[]
     */
    public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): array
    {
        $qb = $this
            ->createQueryBuilder('e')
            ->where('e.deletedAt IS NULL');

        if (is_array($criteria)) {
            foreach ($criteria as $fieldName => $value) {
                $qb
                    ->andWhere("e.{$fieldName} = :{$fieldName}")
                    ->setParameter($fieldName, $value);
            }
        }

        if (is_array($orderBy)) {
            foreach ($orderBy as $fieldName => $order) {
                $qb->addOrderBy("e.{$fieldName}", $order);
            }
        }

        $query = $qb->getQuery();

        if ($limit !== null) {
            $query->setMaxResults($limit);
        }

        if ($offset !== null) {
            $query->setFirstResult($offset);
        }

        return $query->getResult();
    }
}
