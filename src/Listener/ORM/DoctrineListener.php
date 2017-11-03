<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Listener\ORM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;

/**
 * DoctrineListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class DoctrineListener implements EventSubscriber
{

    public function getSubscribedEvents()
    {
        return [
            'onFlush',
        ];
    }
    
    public function onFlush(OnFlushEventArgs $args)
    {
        $em  = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if (method_exists($entity, 'setDeletedAt')) {
                $date = new \DateTime;
                $em->persist($entity);
                $uow->propertyChanged($entity, 'deletedAt', null, $date);
                $uow->scheduleExtraUpdate($entity, [
                    'deletedAt' => [null, $date],
                ]);
            }
        }
    }
}
