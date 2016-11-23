<?php

namespace AppBundle\Repository\ORM;

use Doctrine\ORM\EntityRepository;
use Novosga\Entity\Unidade;
use Novosga\Entity\Usuario;
use Novosga\Entity\Lotacao;
use Novosga\Repository\UnidadeRepositoryInterface;

/**
 * UnidadeRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class UnidadeRepository extends EntityRepository implements UnidadeRepositoryInterface
{
    
    /**
     * Retorna todas as unidades ordenadas por nome
     * 
     * @return Unidade[]
     */
    public function findAll()
    {
        return $this->findBy([], ['nome' => 'ASC']);
    }
    
    /**
     * 
     * @param Usuario $usuario
     * @return Unidade[]
     */
    public function findByUsuario(Usuario $usuario)
    {   
        $unidades = $this->createQueryBuilder('e')
                ->join(Lotacao::class, 'l', 'WITH', 'l.unidade = e')
                ->where('l.usuario = :usuario')
                ->setParameter('usuario', $usuario)
                ->getQuery()
                ->getResult();
        
        return $unidades;
    }
    
}
