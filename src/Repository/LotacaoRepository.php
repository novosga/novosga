<?php

declare(strict_types=1);

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Lotacao;
use Novosga\Entity\LotacaoInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Entity\UsuarioInterface;
use Novosga\Repository\LotacaoRepositoryInterface;

/**
 * @extends ServiceEntityRepository<LotacaoInterface>
 *
 * @method Lotacao|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lotacao|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lotacao[]    findAll()
 * @method Lotacao[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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
     */
    public function getLotacoes(UsuarioInterface $usuario): array
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
            ->setParameter('usuario', $usuario->getId())
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Retorna as lotações do usuário
     */
    public function getLotacoesUnidade(UnidadeInterface $unidade): array
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
     */
    public function getLotacao(UsuarioInterface $usuario, UnidadeInterface $unidade): ?LotacaoInterface
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
            ->setParameter('usuario', $usuario->getId())
            ->setParameter('unidade', $unidade->getId())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
