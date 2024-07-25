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

use App\Entity\SoftDeletableEntityInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Psr\Clock\ClockInterface;

/**
 * DoctrineListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class DoctrineListener implements EventSubscriber
{
    public function __construct(
        private readonly ClockInterface $clock,
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $now = $this->clock->now();

        /** @var EntityManagerInterface */
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof SoftDeletableEntityInterface) {
                $em->persist($entity);
                $uow->propertyChanged($entity, 'deletedAt', null, $now);
                $uow->scheduleExtraUpdate($entity, [
                    'deletedAt' => [null, $now],
                ]);
            }
        }
    }
}
