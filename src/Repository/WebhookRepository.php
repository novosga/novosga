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

use App\Entity\Webhook;
use App\Types\WebhookEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Webhook>
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class WebhookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Webhook::class);
    }

    /** @return Webhook[] */
    public function findEnabledByEvent(WebhookEvent $event): array
    {
        $webhooks = $this
            ->createQueryBuilder('e')
            ->andWhere('e.enabled = TRUE')
            ->getQuery()
            ->getResult()
        ;

        // TODO: add support for JSON column filtering
        return array_values(
            array_filter(
                $webhooks,
                fn(Webhook $webhook) => in_array($event->value, $webhook->getEvents()),
            )
        );
    }
}
