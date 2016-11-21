<?php

namespace AppBundle\Repository\ORM;

use Doctrine\ORM\EntityRepository;
use Novosga\Repository\PrioridadeRepositoryInterface;

/**
 * PrioridadeRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class PrioridadeRepository extends EntityRepository implements PrioridadeRepositoryInterface
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
