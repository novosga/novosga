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

namespace App\Tests\EventSubscriber;

use App\Message\SendWebhookMessage;
use App\Service\AtendimentoService;
use App\Tests\TestHelper;
use Doctrine\ORM\EntityManagerInterface;
use Novosga\Entity\PrioridadeInterface;
use Novosga\Entity\ServicoInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Entity\UsuarioInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

class WebhookSubscriberTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private InMemoryTransport $transport;
    private AtendimentoService $service;

    private UsuarioInterface $usuario;
    private UnidadeInterface $unidade;
    private ServicoInterface $servico;
    private PrioridadeInterface $prioridade;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->transport = self::getContainer()->get('messenger.transport.async');
        $this->service = self::getContainer()->get(AtendimentoService::class);

        TestHelper::removeTestData($this->em);

        $this->usuario = TestHelper::getUser($this->em);
        $this->unidade = TestHelper::createUnidade($this->em);
        $this->servico = TestHelper::createServico($this->em);
        $this->prioridade = TestHelper::createPrioridade($this->em);
        $perfil = TestHelper::createPerfil($this->em);

        TestHelper::linkServicoUnidade($this->em, $this->servico, $this->unidade);
        TestHelper::linkUnidadeUsuario($this->em, $this->unidade, $this->usuario, $perfil);
    }

    public function testTicketMainFlow(): void
    {
        // ticket created
        $atendimento = $this->service->distribuiSenha(
            $this->unidade,
            $this->usuario,
            $this->servico,
            $this->prioridade,
        );

        $this->assertCount(1, $this->transport->getSent());
        $this->assertEventMessage($this->transport->getSent()[0], 'ticket.created', $atendimento->jsonSerialize());

        // first reply
        $local = TestHelper::createLocal($this->em);
        $this->service->chamarAtendimento($atendimento, $this->usuario, $local, 1);

        $this->assertCount(2, $this->transport->getSent());
        $this->assertEventMessage($this->transport->getSent()[1], 'ticket.first_reply', $atendimento->jsonSerialize());

        // ticket started
        $this->service->iniciarAtendimento($atendimento, $this->usuario);

        $this->assertCount(3, $this->transport->getSent());
        $this->assertEventMessage($this->transport->getSent()[2], 'ticket.start', $atendimento->jsonSerialize());

        // ticket finish
        $this->service->encerrar($atendimento, $this->usuario, [$atendimento->getServico()]);

        $this->assertCount(4, $this->transport->getSent());
        $this->assertEventMessage($this->transport->getSent()[3], 'ticket.finished', $atendimento->jsonSerialize());
    }

    /** @param array<string,mixed> $payload */
    private function assertEventMessage(Envelope $envelope, string $event, array $payload): void
    {
        $this->assertInstanceOf(SendWebhookMessage::class, $envelope->getMessage());
        /** @var SendWebhookMessage */
        $message = $envelope->getMessage();
        $this->assertSame($event, $message->event);
        $this->assertSame($payload, $message->payload);
    }
}
