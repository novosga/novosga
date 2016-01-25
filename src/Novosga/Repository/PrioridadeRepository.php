<?php

namespace Novosga\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * PrioridadeRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class PrioridadeRepository extends EntityRepository
{
    
    public function findAtivas()
    {
        return $this
                ->createQueryBuilder('e')
                ->where('e.status = 1 AND e.peso > 0')
                ->orderBy('e.nome', 'ASC')
                ->getQuery()
                ->getResult();
    }
    
}
