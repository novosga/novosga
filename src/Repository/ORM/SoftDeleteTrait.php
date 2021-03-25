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

/**
 * SoftDeleteTrait
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
trait SoftDeleteTrait
{
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $qb = $this
            ->createQueryBuilder('e')
            ->where('e.deletedAt IS NULL');
        
        $params = [];
        
        if (is_array($criteria)) {
            foreach ($criteria as $fieldName => $value) {
                $qb->andWhere("e.{$fieldName} = :{$fieldName}");
                $params[$fieldName] = $value;
            }
        }
        
        if (is_array($orderBy)) {
            foreach ($orderBy as $fieldName => $order) {
                $qb->addOrderBy("e.{$fieldName}", $order);
            }
        }
        
        $query = $qb
                ->setParameters($params)
                ->getQuery();
        
        if ($limit !== null) {
            $query->setMaxResults($limit);
        }
        
        if ($offset !== null) {
            $query->setFirstResult($offset);
        }
        
        return $query->getResult();
    }
}
