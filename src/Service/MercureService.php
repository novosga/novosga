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
                [ 'id' => $unidade->getId() ]
            );
        }

        $this->publish([ "/fila" ], []);
    }

    public function notificaFilaUsuario(UsuarioInterface $usuario): void
    {
        $this->publish(
            [ "/usuarios/{$usuario->getId()}/fila" ],
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

        $this->publish($topics, [ 'id' => $atendimento->getId() ]);
    }

    /**
     * @param string[] $topics
     * @param array<string,mixed> $params
     */
    private function publish(array $topics, array $params): void
    {
        try {
            // Add @type field based on the primary topic for client identification
            $messageData = $params;
            if (!empty($topics)) {
                $messageData['@type'] = $this->extractMessageType($topics[0]);
            }
            
            $this->hub->publish(new Update($topics, json_encode($messageData)));
        } catch (RuntimeException $ex) {
            $this->logger->error($ex->getMessage());
        }
    }

    /**
     * Extract message type from topic pattern for client identification
     */
    private function extractMessageType(string $topic): string
    {
        // Extract type from topic patterns
        if ($topic === '/fila') {
            return 'queue.general';
        }
        if (preg_match('/^\/unidades\/\d+\/fila$/', $topic)) {
            return 'queue.unit';
        }
        if (preg_match('/^\/usuarios\/\d+\/fila$/', $topic)) {
            return 'queue.user';
        }
        if ($topic === '/paineis') {
            return 'panel.general';
        }
        if (preg_match('/^\/unidades\/\d+\/painel$/', $topic)) {
            return 'panel.unit';
        }
        if (preg_match('/^\/atendimentos\/\d+$/', $topic)) {
            return 'attendance';
        }
        
        // Fallback for unknown patterns
        return 'unknown';
    }
}
