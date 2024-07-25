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
use Doctrine\Common\Collections\Criteria;
use App\Entity\Usuario;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Novosga\Entity\ServicoUnidadeInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Entity\UsuarioInterface;
use Novosga\Repository\UsuarioRepositoryInterface;

/**
 * @extends ServiceEntityRepository<UsuarioInterface>
 *
 * @method Usuario|null find($id, $lockMode = null, $lockVersion = null)
 * @method Usuario|null findOneBy(array $criteria, array $orderBy = null)
 * @method Usuario[]    findAll()
 * @method Usuario[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class UsuarioRepository extends ServiceEntityRepository implements UsuarioRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Usuario::class);
    }

    public function findOneByLogin(string $login): ?Usuario
    {
        return $this->findOneBy(['login' => $login]);
    }

    /** {@inheritdoc} */
    public function findByUnidade(UnidadeInterface $unidade, Criteria $criteria = null): array
    {
        $usuarios = $this
            ->queryBuilderFindByUnidade($unidade, $criteria)
            ->getQuery()
            ->getResult();

        return $usuarios;
    }

    /** @return Usuario[] */
    public function findByServicoUnidade(ServicoUnidadeInterface $servicoUnidade, Criteria $criteria = null): array
    {
        $unidade  = $servicoUnidade->getUnidade();
        $servico  = $servicoUnidade->getServico();
        $usuarios = $this
            ->queryBuilderFindByUnidade($unidade, $criteria)
            ->join(\App\Entity\ServicoUsuario::class, 'su', 'WITH', 'su.usuario = e')
            ->andWhere('su.servico = :servico')
            ->setParameter('servico', $servico)
            ->getQuery()
            ->getResult();

        return $usuarios;
    }

    private function queryBuilderFindByUnidade(UnidadeInterface $unidade, ?Criteria $criteria = null): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder('e')
            ->leftJoin('e.lotacoes', 'l')
            ->where('e.admin = TRUE OR (e.admin = FALSE AND l.unidade = :unidade)')
            ->setParameter('unidade', $unidade)
            ->orderBy('e.nome');

        if ($criteria !== null) {
            $qb->addCriteria($criteria);
        }

        return $qb;
    }
}
