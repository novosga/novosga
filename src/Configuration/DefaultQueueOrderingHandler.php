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

namespace App\Configuration;

use Novosga\Event\QueueOrderingEvent;
use Psr\Container\ContainerInterface;

/**
 * DefaultQueueOrdering
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class DefaultQueueOrderingHandler
{
    public function __invoke(QueueOrderingEvent $event, ContainerInterface $container): void
    {
        $qb = $event->queryBuilder;
        $qb->addOrderBy('atendimento.dataAgendamento', 'ASC');

        if ($event->usuario) {
            // peso servico x usuario
            $qb->addOrderBy('servicoUsuario.peso', 'DESC');
        }

        $qb
            ->addOrderBy('prioridade.peso', 'DESC') // priority
            ->addOrderBy('servicoUnidade.peso', 'DESC') // peso servico x unidade
            ->addOrderBy('atendimento.dataChegada', 'DESC') // dataChegada
        ;
    }
}
