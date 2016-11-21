<?php

namespace AppBundle\Repository\ORM;

use Doctrine\ORM\EntityRepository;
use Novosga\Entity\Cargo;
use Novosga\Repository\ClienteRepositoryInterface;

/**
 * ClienteRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class ClienteRepository extends EntityRepository implements ClienteRepositoryInterface
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
