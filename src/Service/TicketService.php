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

use App\Repository\ServicoUnidadeRepository;
use Novosga\Entity\AtendimentoInterface;
use Novosga\Service\TicketServiceInterface;
use Psr\Clock\ClockInterface;
use Twig\Environment;

/**
 * TicketService
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class TicketService implements TicketServiceInterface
{
    private const DEFAULT_TEMPLATE = 'print.html.twig';

    public function __construct(
        private readonly ClockInterface $clock,
        private readonly ServicoUnidadeRepository $servicoUnidadeRepository,
        private readonly Environment $twig,
    ) {
    }

    /**
     * Imprime a senha informada pelo atendimento.
     */
    public function printTicket(AtendimentoInterface $atendimento): string
    {
        $unidade = $atendimento->getUnidade();
        $servico = $atendimento->getServico();

        $su = $this->servicoUnidadeRepository->get($unidade, $servico);

        $viewParams = [
            'atendimento' => $atendimento,
            'servicoUnidade' => $su,
            'now' => $this->clock->now(),
        ];

        return $this->twig->render(self::DEFAULT_TEMPLATE, $viewParams);
    }
}
