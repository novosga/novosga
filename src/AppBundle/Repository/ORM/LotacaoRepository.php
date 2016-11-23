<?php

namespace AppBundle\Repository\ORM;

use Doctrine\ORM\EntityRepository;
use Novosga\Entity\Lotacao;
use Novosga\Entity\Unidade;
use Novosga\Entity\Usuario;
use Novosga\Repository\LotacaoRepositoryInterface;

/**
 * LotacaoRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class LotacaoRepository extends EntityRepository implements LotacaoRepositoryInterface
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
                ->where("e.usuario = :usuario")
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
                ->where("e.usuario = :usuario")
                ->andWhere("e.unidade = :unidade")
                ->setParameter('usuario', $usuario)
                ->setParameter('unidade', $unidade)
                ->getQuery()
                ->getOneOrNullResult()
        ;
        
    }
    
}
