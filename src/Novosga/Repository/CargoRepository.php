<?php

namespace Novosga\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * CargoRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class CargoRepository extends EntityRepository
{
    
    /**
     * Retorna todos os cargos ordenados por nome
     * 
     * @return \AppBundle\Entity\Cargo[]
     */
    public function findAll()
    {
        return $this->findBy([], ['nome' => 'ASC']);
    }
    
}
