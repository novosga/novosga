<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Infrastructure\Storage;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Novosga\Infrastructure\StorageInterface;

/**
 * Doctrine Storage
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class DoctrineStorage implements StorageInterface
{
    /**
     * @var ObjectManager
     */
    protected $om;
    
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }
    
    public function getManager(): ObjectManager
    {
        return $this->om;
    }

    public function getRepository(string $className): ObjectRepository
    {
        return $this->om->getRepository($className);
    }
}
