<?php

namespace Novosga\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * GrupoRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class GrupoRepository extends EntityRepository
{
    
    /**
     * Retorna todos os grupos ordenados por nome
     * 
     * @return \AppBundle\Entity\Grupo[]
     */
    public function findAll()
    {
        return $this->findBy([], ['nome' => 'ASC']);
    }
    
}
