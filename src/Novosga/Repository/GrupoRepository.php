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
     * Retorna todos os grupos ordenados pelo nível e pelo nome
     * 
     * @return \Novosga\Entity\Grupo[]
     */
    public function findAll()
    {
        return $this->findBy([], ['level' => 'ASC', 'nome' => 'ASC']);
    }
    
}
