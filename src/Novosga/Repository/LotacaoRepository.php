<?php

namespace Novosga\Repository;

use Doctrine\ORM\EntityRepository;
use Novosga\Entity\Lotacao;
use Novosga\Entity\Usuario;
use Novosga\Entity\Unidade;

/**
 * LotacaoRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class LotacaoRepository extends EntityRepository
{
    
    /**
     * Retorna as lotações do usuário
     * 
     * @param Usuario $usuario
     * @param Unidade $unidade
     * @return Lotacao
     */
    public function getLotacoes(Usuario $usuario)
    {
        return $this->getEntityManager()
                ->createQueryBuilder()
                ->select('e')
                ->from($this->getEntityName(), 'e')
                ->join('e.cargo', 'c')
                ->join('e.grupo', 'g')
                ->where("e.usuario = :usuario")
                ->orderBy('g.left', 'DESC')
                ->setParameter('usuario', $usuario)
                ->getQuery()
                ->getOneOrNullResult()
        ;
        
    }
    
    /**
     * Retorna a lotação do usuário na unidade
     * 
     * @param Usuario $usuario
     * @param Unidade $unidade
     * @return Lotacao
     */
    public function getLotacao(Usuario $usuario, Unidade $unidade)
    {
        return $this->getEntityManager()
                ->createQueryBuilder()
                ->select('e')
                ->from($this->getEntityName(), 'e')
                ->join('e.cargo', 'c')
                ->join('e.grupo', 'g')
                ->where("e.usuario = :usuario AND e.grupo = :grupo")
                ->orderBy('g.left', 'DESC')
                ->setParameter('usuario', $usuario)
                ->setParameter('grupo', $unidade->getGrupo())
                ->getQuery()
                ->getOneOrNullResult()
        ;
        
    }
    
}
