<?php

namespace AppBundle\Repository\ORM;

use Doctrine\ORM\EntityRepository;
use Novosga\Entity\Cargo;
use Novosga\Repository\CargoRepositoryInterface;

/**
 * CargoRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class CargoRepository extends EntityRepository implements CargoRepositoryInterface
{
    
    /**
     * Retorna todos os cargos ordenados pelo nível e pelo nome
     * 
     * @return Cargo[]
     */
    public function findAll()
    {
        return $this->findBy([], ['nome' => 'ASC']);
    }
    
}
