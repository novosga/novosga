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
use App\Entity\Metadata;
use Novosga\Entity\MetadataInterface;
use Novosga\Repository\MetadataRepositoryInterface;

/**
 * @template T of Metadata
 * @extends ServiceEntityRepository<T>
 * @implements MetadataRepositoryInterface<T>
 *
 * @method T|null find($id, $lockMode = null, $lockVersion = null)
 * @method T|null findOneBy(array $criteria, array $orderBy = null)
 * @method T[]    findAll()
 * @method T[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class MetadataRepository extends ServiceEntityRepository implements MetadataRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Metadata::class);
    }

    /** @return ?T */
    public function get(string $namespace, string $name): ?MetadataInterface
    {
        return $this->findOneBy([
            'namespace' => $namespace,
            'name'      => $name
        ]);
    }

    /** @return T */
    public function set(string $namespace, string $name, mixed $value = null): MetadataInterface
    {
        $em = $this->getEntityManager();
        $metada = $this->get($namespace, $name);

        if ($metada instanceof Metadata) {
            $metada->setValue($value);
        } else {
            $class  = $this->getEntityName();
            $metada = new $class();
            $metada->setNamespace($namespace);
            $metada->setName($name);
            $metada->setValue($value);
        }

        $em->persist($metada);
        $em->flush();

        return $metada;
    }
}
