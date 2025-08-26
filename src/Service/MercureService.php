<?php

declare(strict_types=1);

/*
 * This file is part of the NovoSGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Dto\MercureEvent;
use Novosga\Entity\AtendimentoInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Entity\UsuarioInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\Exception\RuntimeException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

/**
 * MercureService
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class MercureService
{
    public function __construct(
        private readonly HubInterface $hub,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function notificaFilaUnidade(?UnidadeInterface $unidade): void
    {
        if ($unidade !== null) {
            $this->publish(
                [ "/unidades/{$unidade->getId()}/fila" ],
                'queue.unity',
                [ 'id' => $unidade->getId() ],
            );
        }

        $this->publish([ "/fila" ], 'queue.global', []);
    }

    public function notificaFilaUsuario(UsuarioInterface $usuario): void
    {
        $this->publish(
            [ "/usuarios/{$usuario->getId()}/fila" ],
            'queue.user',
            [ 'id' => $usuario->getId() ],
        );
    }

    public function notificaPainel(AtendimentoInterface $atendimento): void
    {
        $this->publish(
            [
                '/paineis',
                "/unidades/{$atendimento->getUnidade()->getId()}/painel",
            ],
            'panel.ticket',
            [
                'id' => $atendimento->getId(),
            ],
        );
    }

    public function notificaAtendimento(AtendimentoInterface $atendimento, ?UsuarioInterface $usuario = null): void
    {
        $topics = [
            "/atendimentos/{$atendimento->getId()}",
            "/unidades/{$atendimento->getUnidade()->getId()}/fila",
        ];

        if ($usuario) {
            $topics[] = "/usuarios/{$usuario->getId()}/fila";
        }

        $this->publish($topics, 'ticket', [ 'id' => $atendimento->getId() ]);
    }

    /**
     * @param string[] $topics
     * @param array<string,mixed> $payload
     */
    private function publish(array $topics, string $type, array $payload): void
    {
        try {
            $event = new MercureEvent($type, $payload);
            $this->hub->publish(new Update($topics, json_encode($event)));
        } catch (RuntimeException $ex) {
            $this->logger->error($ex->getMessage());
        }
    }
}
