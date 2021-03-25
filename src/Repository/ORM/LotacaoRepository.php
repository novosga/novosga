<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository\ORM;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Novosga\Entity\Lotacao;
use Novosga\Entity\Unidade;
use Novosga\Entity\Usuario;
use Novosga\Repository\LotacaoRepositoryInterface;

/**
 * LotacaoRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class LotacaoRepository extends ServiceEntityRepository implements LotacaoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lotacao::class);
    }
    
    /**
     * Retorna as lotações do usuário
     *
     * @param Usuario $usuario
     * @return Lotacao[]
     */
    public function getLotacoes(Usuario $usuario)
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select([
                'e', 'c', 'u'
            ])
            ->from($this->getEntityName(), 'e')
            ->join('e.perfil', 'c')
            ->join('e.unidade', 'u')
            ->where("e.usuario = :usuario")
            ->setParameter('usuario', $usuario)
            ->getQuery()
            ->getResult()
        ;
    }
    
    /**
     * Retorna as lotações do usuário
     *
     * @param Unidade $unidade
     * @return Lotacao[]
     */
    public function getLotacoesUnidade(Unidade $unidade)
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select([
                'e', 'c', 'u'
            ])
            ->from($this->getEntityName(), 'e')
            ->join('e.perfil', 'c')
            ->join('e.usuario', 'u')
            ->where("e.unidade = :unidade")
            ->setParameter('unidade', $unidade)
            ->getQuery()
            ->getResult()
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
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select([
                'e', 'c', 'u'
            ])
            ->from($this->getEntityName(), 'e')
            ->join('e.perfil', 'c')
            ->join('e.unidade', 'u')
            ->where("e.usuario = :usuario")
            ->andWhere("e.unidade = :unidade")
            ->setParameter('usuario', $usuario)
            ->setParameter('unidade', $unidade)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
