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
use App\Entity\Unidade;
use App\Entity\Lotacao;
use Novosga\Entity\UnidadeInterface;
use Novosga\Entity\UsuarioInterface;
use Novosga\Repository\UnidadeRepositoryInterface;

/**
 * @extends ServiceEntityRepository<UnidadeInterface>
 *
 * @method Unidade|null find($id, $lockMode = null, $lockVersion = null)
 * @method Unidade|null findOneBy(array $criteria, array $orderBy = null)
 * @method Unidade[]    findAll()
 * @method Unidade[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class UnidadeRepository extends ServiceEntityRepository implements UnidadeRepositoryInterface
{
    /** @use SoftDeleteTrait<Unidade> */
    use SoftDeleteTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Unidade::class);
    }

    /**
     * Retorna todas as unidades ordenadas por nome
     * @return Unidade[]
     */
    public function findAll(): array
    {
        return $this->findBy([], ['nome' => 'ASC']);
    }

    /** {@inheritdoc} */
    public function findByUsuario(UsuarioInterface $usuario): array
    {
        $qb = $this
            ->createQueryBuilder('e')
            ->where('e.deletedAt IS NULL');

        if (!$usuario->isAdmin()) {
            $qb
                ->join(Lotacao::class, 'l', 'WITH', 'l.unidade = e')
                ->where('l.usuario = :usuario')
                ->setParameter('usuario', $usuario);
        }

        $unidades = $qb
            ->getQuery()
            ->getResult();

        return $unidades;
    }
}
