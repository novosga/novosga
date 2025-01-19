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

namespace App\Service;

use Doctrine\ORM\QueryBuilder;
use Novosga\Entity\UnidadeInterface;
use Novosga\Entity\UsuarioInterface;
use Novosga\Service\QueueOrderingServiceInterface;
use Psr\Clock\ClockInterface;

/**
 * DefaultQueueOrdering.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class DefaultQueueOrderingService implements QueueOrderingServiceInterface
{
    public function __construct(
        private readonly ClockInterface $clock,
        private readonly ApplicationSettingsService $service,
        private readonly TicketPrioritySwapService $prioritySwapService,
    ) {
    }

    public function applyOrder(QueryBuilder $queryBuilder, UnidadeInterface $unidade, ?UsuarioInterface $usuario): void
    {
        $settings = $this->service->loadQueueSettings();
        foreach ($settings->ordering as $option) {
            $field = $option['field'] ?? '';
            $sort = $option['order'] ?? 'ASC';
            switch ($field) {
                case 'dataAgendamento':
                    $queryBuilder->addOrderBy('atendimento.dataAgendamento', $sort);
                    break;
                case 'dataChegada':
                    $queryBuilder->addOrderBy('atendimento.dataChegada', $sort);
                    break;
                case 'prioridade':
                    // peso prioridade
                    if (!$this->prioritySwapService->shouldIgnorePriority($unidade, $usuario)) {
                        $queryBuilder->addOrderBy('prioridade.peso', $sort);
                    }
                    break;
                case 'servicoUsuario':
                    if ($usuario) {
                        // peso servico x usuario
                        $queryBuilder->addOrderBy('servicoUsuario.peso', $sort);
                    }
                    break;
                case 'servicoUnidade':
                        // peso servico x unidade
                        $queryBuilder->addOrderBy('servicoUnidade.peso', $sort);
                    break;
                case 'balanceamento':
                    // balanceamento do tempo de chegada x prioridade
                    if (!$this->prioritySwapService->shouldIgnorePriority($unidade, $usuario)) {
                        $exp = '((UNIX_TIMESTAMP(:now) - UNIX_TIMESTAMP(atendimento.dataChegada))' .
                                    ' * (prioridade.peso + 1))';
                        $queryBuilder
                            ->addOrderBy($exp, $sort)
                            ->setParameter('now', $this->clock->now());
                    }
                    break;
            }
        }
    }
}
