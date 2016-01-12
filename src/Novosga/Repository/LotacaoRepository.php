<?php

namespace Novosga\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * LotacaoRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class LotacaoRepository extends EntityRepository
{
    
    /**
     * 
     * @param \AppBundle\Entity\Usuario $usuario
     * @return \AppBundle\Entity\Lotacao[]
     */
    public function getLotacoes($usuario)
    {
        return $this->getEntityManager()
                ->createQueryBuilder()
                ->select('e')
                ->from($this->getEntityName(), 'e')
                ->join('e.cargo', 'c')
                ->join('e.grupo', 'g')
                ->where("e.usuario = :usuario")
                ->orderBy('g.left', 'DESC')
        ;
        
    }
    
}
