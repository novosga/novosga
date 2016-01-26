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
     * Retorna todos os cargos ordenados pelo nível e pelo nome
     * 
     * @return \Novosga\Entity\Cargo[]
     */
    public function findAll()
    {
        return $this->findBy([], ['level' => 'ASC', 'nome' => 'ASC']);
    }
    
}
