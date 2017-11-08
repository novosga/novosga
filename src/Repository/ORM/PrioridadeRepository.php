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

use Doctrine\ORM\EntityRepository;
use Novosga\Repository\PrioridadeRepositoryInterface;

/**
 * PrioridadeRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class PrioridadeRepository extends EntityRepository implements PrioridadeRepositoryInterface
{
    use SoftDeleteTrait;
    
    public function findAtivas()
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
