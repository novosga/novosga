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
use App\Entity\Perfil;
use Novosga\Entity\PerfilInterface;
use Novosga\Repository\PerfilRepositoryInterface;

/**
 * @extends ServiceEntityRepository<PerfilInterface>
 *
 * @method Perfil|null find($id, $lockMode = null, $lockVersion = null)
 * @method Perfil|null findOneBy(array $criteria, array $orderBy = null)
 * @method Perfil[]    findAll()
 * @method Perfil[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class PerfilRepository extends ServiceEntityRepository implements PerfilRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Perfil::class);
    }

    /**
     * Retorna todos os perfis ordenados pelo nível e pelo nome
     * @return Perfil[]
     */
    public function findAll(): array
    {
        return $this->findBy([], ['nome' => 'ASC']);
    }
}
