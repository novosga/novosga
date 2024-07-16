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

namespace App\EventListener;

use App\Entity\TimestampableEntityInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Psr\Clock\ClockInterface;

/**
 * TimestampableEntityListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[AsEntityListener]
class TimestampableEntityListener
{
    public function __construct(
        private readonly ClockInterface $clock,
    ) {
    }

    public function preUpdate(TimestampableEntityInterface $entity, PreUpdateEventArgs $event): void
    {
        $entity->setUpdatedAt($this->clock->now());
    }

    public function prePersist(TimestampableEntityInterface $entity, PrePersistEventArgs $event): void
    {
        $entity->setCreatedAt($this->clock->now());
    }
}
