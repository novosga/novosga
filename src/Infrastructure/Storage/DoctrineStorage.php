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

namespace App\Infrastructure\Storage;

use Novosga\Infrastructure\StorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Doctrine Storage
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class DoctrineStorage implements StorageInterface
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    public function getManager(): EntityManagerInterface
    {
        return $this->em;
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @return EntityRepository<T>
     */
    public function getRepository(string $className): EntityRepository
    {
        return $this->em->getRepository($className);
    }
}
