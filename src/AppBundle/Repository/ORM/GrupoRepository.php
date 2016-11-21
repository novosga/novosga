<?php

namespace AppBundle\Repository\ORM;

use Doctrine\ORM\EntityRepository;
use Novosga\Entity\Grupo;
use Novosga\Repository\GrupoRepositoryInterface;

/**
 * GrupoRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class GrupoRepository extends EntityRepository implements GrupoRepositoryInterface
{
    
    /**
     * Retorna todos os grupos ordenados pelo nível e pelo nome
     * 
     * @return Grupo[]
     */
    public function findAll()
    {
        return $this->findBy([], ['level' => 'ASC', 'nome' => 'ASC']);
    }
    
}
