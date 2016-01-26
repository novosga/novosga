<?php

namespace Novosga\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * UnidadeRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class UnidadeRepository extends EntityRepository
{
    
    /**
     * Retorna todas as unidades ordenadas por nome
     * 
     * @return \Novosga\Entity\Unidade[]
     */
    public function findAll()
    {
        return $this->findBy([], ['nome' => 'ASC']);
    }
    
}
