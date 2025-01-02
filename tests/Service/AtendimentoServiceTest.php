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

namespace App\Tests\Service;

use App\Entity\Agendamento;
use App\Entity\Atendimento;
use App\Entity\Cliente;
use App\Entity\Local;
use App\Entity\Lotacao;
use App\Entity\PainelSenha;
use App\Entity\Prioridade;
use App\Entity\Senha;
use App\Entity\Servico;
use App\Entity\ServicoUnidade;
use App\Entity\Unidade;
use App\Entity\Usuario;
use App\Repository\AtendimentoMetadataRepository;
use App\Repository\AtendimentoRepository;
use App\Repository\ClienteRepository;
use App\Repository\LotacaoRepository;
use App\Repository\ServicoUnidadeRepository;
use App\Service\AtendimentoService;
use App\Service\FilaService;
use App\Service\MercureService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Novosga\Event\PreTicketCallEvent;
use Novosga\Event\PreTicketCreateEvent;
use Novosga\Event\TicketCalledEvent;
use Novosga\Event\TicketCreatedEvent;
use Novosga\Infrastructure\StorageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * AtendimentoServiceTest
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class AtendimentoServiceTest extends TestCase
{
    private const TEST_LOCALE = 'pt_BR';

    private ClockInterface $clock;
    private StorageInterface&MockObject $storage;
    private EventDispatcherInterface&MockObject $dispatcher;
    private LoggerInterface $logger;
    private Translator $translator;
    private MercureService&MockObject $mercureService;
    private FilaService&MockObject $filaService;
    private AtendimentoRepository&MockObject $atendimentoRepository;
    private AtendimentoMetadataRepository&MockObject $atendimentoMetaRepository;
    private ServicoUnidadeRepository&MockObject $servicoUnidadeRepository;
    private ClienteRepository&MockObject $clienteRepository;

    private AtendimentoService $service;

    protected function setUp(): void
    {
        $this->clock = new MockClock();
        $this->storage = $this->createMock(StorageInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->logger = new NullLogger();
        $this->translator = new Translator(self::TEST_LOCALE);
        $this->mercureService = $this->createMock(MercureService::class);
        $this->filaService = $this->createMock(FilaService::class);
        $this->atendimentoRepository = $this->createMock(AtendimentoRepository::class);
        $this->atendimentoMetaRepository = $this->createMock(AtendimentoMetadataRepository::class);
        $this->servicoUnidadeRepository = $this->createMock(ServicoUnidadeRepository::class);
        $this->clienteRepository = $this->createMock(ClienteRepository::class);

        $this->translator->addLoader('array', new ArrayLoader());

        $this->service = new AtendimentoService(
            $this->clock,
            $this->storage,
            $this->dispatcher,
            $this->logger,
            $this->translator,
            $this->mercureService,
            $this->filaService,
            $this->atendimentoRepository,
            $this->atendimentoMetaRepository,
            $this->servicoUnidadeRepository,
            $this->clienteRepository,
        );
    }

    public function testChamarSenha(): void
    {
        $usuario = new Usuario();
        $atendimento = $this->buildAtendimento();
        $servicoUnidade = (new ServicoUnidade())->setMensagem('Message test');

        $this
            ->servicoUnidadeRepository
            ->expects($this->once())
            ->method('get')
            ->with($atendimento->getUnidade(), $atendimento->getServico())
            ->willReturn($servicoUnidade);

        /** @var EntityManagerInterface&MockObject */
        $em = $this->createMock(EntityManagerInterface::class);

        $this
            ->storage
            ->expects($this->once())
            ->method('getManager')
            ->willReturn($em);

        $em
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(PainelSenha::class))
            ->willReturnCallback(function (PainelSenha $painelSenha) use ($atendimento, $servicoUnidade) {
                $this->assertEquals($atendimento->getSenha()->getNumero(), $painelSenha->getNumeroSenha());
                $this->assertEquals($atendimento->getSenha()->getSigla(), $painelSenha->getSiglaSenha());
                $this->assertEquals($atendimento->getCliente()->getNome(), $painelSenha->getNomeCliente());
                $this->assertEquals($atendimento->getCliente()->getDocumento(), $painelSenha->getDocumentoCliente());
                $this->assertEquals($atendimento->getLocal()->getNome(), $painelSenha->getLocal());
                $this->assertEquals($atendimento->getNumeroLocal(), $painelSenha->getNumeroLocal());
                $this->assertEquals($servicoUnidade->getMensagem(), $painelSenha->getMensagem());
            });

        $this
            ->dispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [ $this->isInstanceOf(PreTicketCallEvent::class) ],
                [ $this->isInstanceOf(TicketCalledEvent::class) ],
            );

        $this
            ->mercureService
            ->expects($this->once())
            ->method('notificaPainel')
            ->with($this->equalTo($atendimento));

        $this->service->chamarSenha($atendimento, $usuario);
    }

    public function testDistribuiSenhaWithInvalidUnity(): void
    {
        $unidade = 123;
        $usuario = new Usuario();
        $servico = new Servico();
        $prioridade = new Prioridade();

        /** @var EntityManagerInterface&MockObject */
        $em = $this->createMock(EntityManagerInterface::class);

        $this
            ->storage
            ->expects($this->once())
            ->method('getManager')
            ->willReturn($em);

        $em
            ->expects($this->once())
            ->method('find')
            ->with(
                $this->equalTo(Unidade::class),
                $this->equalTo($unidade),
            )
            ->willReturn(null);

        $this->translator->addResource('array', [
            'error.invalid_unity' => 'Unidade inválida',
        ], self::TEST_LOCALE);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unidade inválida');

        $this->service->distribuiSenha(
            $unidade,
            $usuario,
            $servico,
            $prioridade
        );
    }

    public function testDistribuiSenhaWithInvalidUser(): void
    {
        $unidade = new Unidade();
        $usuario = 123;
        $servico = new Servico();
        $prioridade = new Prioridade();

        /** @var EntityManagerInterface&MockObject */
        $em = $this->createMock(EntityManagerInterface::class);

        $this
            ->storage
            ->expects($this->once())
            ->method('getManager')
            ->willReturn($em);

        $em
            ->expects($this->once())
            ->method('find')
            ->with(
                $this->equalTo(Usuario::class),
                $this->equalTo($usuario),
            )
            ->willReturn(null);

        $this->translator->addResource('array', [
            'error.invalid_user' => 'Usuário inválido',
        ], self::TEST_LOCALE);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Usuário inválido');

        $this->service->distribuiSenha(
            $unidade,
            $usuario,
            $servico,
            $prioridade
        );
    }

    public function testDistribuiSenhaWithInvalidService(): void
    {
        $unidade = new Unidade();
        $usuario = new Usuario();
        $servico = 111;
        $prioridade = new Prioridade();

        /** @var EntityManagerInterface&MockObject */
        $em = $this->createMock(EntityManagerInterface::class);

        $this
            ->storage
            ->expects($this->once())
            ->method('getManager')
            ->willReturn($em);

        $em
            ->expects($this->once())
            ->method('find')
            ->with(
                $this->equalTo(Servico::class),
                $this->equalTo($servico),
            )
            ->willReturn(null);

        $this->translator->addResource('array', [
            'error.invalid_service' => 'Serviço inválido',
        ], self::TEST_LOCALE);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Serviço inválido');

        $this->service->distribuiSenha(
            $unidade,
            $usuario,
            $servico,
            $prioridade
        );
    }

    public function testDistribuiSenhaWithInvalidPriority(): void
    {
        $unidade = new Unidade();
        $usuario = new Usuario();
        $servico = new Servico();
        $prioridade = 123;

        /** @var EntityManagerInterface&MockObject */
        $em = $this->createMock(EntityManagerInterface::class);

        $this
            ->storage
            ->expects($this->once())
            ->method('getManager')
            ->willReturn($em);

        $em
            ->expects($this->once())
            ->method('find')
            ->with(
                $this->equalTo(Prioridade::class),
                $this->equalTo($prioridade),
            )
            ->willReturn(null);

        $this->translator->addResource('array', [
            'error.invalid_priority' => 'Prioridade inválida',
        ], self::TEST_LOCALE);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Prioridade inválida');

        $this->service->distribuiSenha(
            $unidade,
            $usuario,
            $servico,
            $prioridade
        );
    }

    public function testDistribuiSenhaWhenPriorityIsDisabled(): void
    {
        $unidade = new Unidade();
        $usuario = new Usuario();
        $servico = new Servico();
        $prioridade = (new Prioridade())->setAtivo(false);

        /** @var EntityManagerInterface&MockObject */
        $em = $this->createMock(EntityManagerInterface::class);

        $this
            ->storage
            ->expects($this->once())
            ->method('getManager')
            ->willReturn($em);

        $this->translator->addResource('array', [
            'error.invalid_priority' => 'Prioridade inválida',
        ], self::TEST_LOCALE);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Prioridade inválida');

        $this->service->distribuiSenha(
            $unidade,
            $usuario,
            $servico,
            $prioridade
        );
    }

    public function testDistribuiSenhaWhenUserDoesNotHavePermission(): void
    {
        $unidade = new Unidade();
        $usuario = new Usuario();
        $servico = new Servico();
        $prioridade = new Prioridade();

        /** @var EntityManagerInterface&MockObject */
        $em = $this->createMock(EntityManagerInterface::class);

        $this
            ->storage
            ->expects($this->once())
            ->method('getManager')
            ->willReturn($em);

        $this->translator->addResource('array', [
            'error.user_unity_ticket_permission' => 'O usuário não tem permissão',
        ], self::TEST_LOCALE);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('O usuário não tem permissão');

        $this->service->distribuiSenha(
            $unidade,
            $usuario,
            $servico,
            $prioridade
        );
    }

    public function testDistribuiSenhaWithInvalidServiceUnity(): void
    {
        $unidade = new Unidade();
        $usuario = new Usuario();
        $servico = new Servico();
        $prioridade = new Prioridade();
        $lotacao = new Lotacao();

        /** @var EntityManagerInterface&MockObject */
        $em = $this->createMock(EntityManagerInterface::class);
        /** @var LotacaoRepository&MockObject */
        $lotacaoRepository = $this->createMock(LotacaoRepository::class);

        $this
            ->storage
            ->expects($this->once())
            ->method('getManager')
            ->willReturn($em);

        $em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo(Lotacao::class))
            ->willReturn($lotacaoRepository);

        $lotacaoRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with([
                'usuario' => $usuario,
                'unidade' => $unidade,
            ])
            ->willReturn($lotacao);

        $this
            ->servicoUnidadeRepository
            ->expects($this->once())
            ->method('get')
            ->with($unidade, $servico)
            ->willReturn(null);

        $this->translator->addResource('array', [
            'error.service_unity_invalid' => 'Serviço unidade inválido',
        ], self::TEST_LOCALE);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Serviço unidade inválido');

        $this->service->distribuiSenha(
            $unidade,
            $usuario,
            $servico,
            $prioridade
        );
    }

    public function testDistribuiSenhaWhenInvalidServiceUnityIsDisabled(): void
    {
        $unidade = new Unidade();
        $usuario = new Usuario();
        $servico = new Servico();
        $prioridade = new Prioridade();
        $lotacao = new Lotacao();
        $servicoUnidade = (new ServicoUnidade())->setAtivo(false);

        /** @var EntityManagerInterface&MockObject */
        $em = $this->createMock(EntityManagerInterface::class);
        /** @var LotacaoRepository&MockObject */
        $lotacaoRepository = $this->createMock(LotacaoRepository::class);

        $this
            ->storage
            ->expects($this->once())
            ->method('getManager')
            ->willReturn($em);

        $em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo(Lotacao::class))
            ->willReturn($lotacaoRepository);

        $lotacaoRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with([
                'usuario' => $usuario,
                'unidade' => $unidade,
            ])
            ->willReturn($lotacao);

        $this
            ->servicoUnidadeRepository
            ->expects($this->once())
            ->method('get')
            ->with($unidade, $servico)
            ->willReturn($servicoUnidade);

        $this->translator->addResource('array', [
            'error.service_unity_inactive' => 'Serviço unidade inativo',
        ], self::TEST_LOCALE);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Serviço unidade inativo');

        $this->service->distribuiSenha(
            $unidade,
            $usuario,
            $servico,
            $prioridade
        );
    }

    public function testDistribuiSenha(): void
    {
        $expectedId = 1000;
        $unidade = new Unidade();
        $usuario = new Usuario();
        $servico = new Servico();
        $prioridade = new Prioridade();
        $lotacao = new Lotacao();
        $servicoUnidade = new ServicoUnidade();

        /** @var EntityManagerInterface&MockObject */
        $em = $this->createMock(EntityManagerInterface::class);
        /** @var LotacaoRepository&MockObject */
        $lotacaoRepository = $this->createMock(LotacaoRepository::class);

        $this
            ->storage
            ->expects($this->once())
            ->method('getManager')
            ->willReturn($em);

        $em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo(Lotacao::class))
            ->willReturn($lotacaoRepository);

        $lotacaoRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with([
                'usuario' => $usuario,
                'unidade' => $unidade,
            ])
            ->willReturn($lotacao);

        $this
            ->servicoUnidadeRepository
            ->expects($this->once())
            ->method('get')
            ->with($unidade, $servico)
            ->willReturn($servicoUnidade);

        $this
            ->dispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [ $this->isInstanceOf(PreTicketCreateEvent::class) ],
                [ $this->isInstanceOf(TicketCreatedEvent::class) ],
            );

        $this
            ->storage
            ->expects($this->once())
            ->method('distribui')
            ->with(
                $this->isInstanceOf(Atendimento::class),
                $this->isNull(), // agendamento is null
            )
            ->willReturnCallback(function (Atendimento $atendimento) use ($expectedId) {
                $atendimento->setId($expectedId);
            });

        $this
            ->mercureService
            ->expects($this->once())
            ->method('notificaAtendimento')
            ->with($this->isInstanceOf(Atendimento::class));

        $atendimento = $this->service->distribuiSenha(
            $unidade,
            $usuario,
            $servico,
            $prioridade
        );

        $this->assertSame($expectedId, $atendimento->getId());
        $this->assertSame($unidade, $atendimento->getUnidade());
        $this->assertSame($usuario, $atendimento->getUsuarioTriagem());
        $this->assertSame($servico, $atendimento->getServico());
        $this->assertSame($prioridade, $atendimento->getPrioridade());
        $this->assertSame(AtendimentoService::SENHA_EMITIDA, $atendimento->getStatus());
        $this->assertNull($atendimento->getCliente());
    }

    public function testDistribuiSenhaWithCustomer(): void
    {
        $unidade = new Unidade();
        $usuario = (new Usuario())->setAdmin(true);
        $servico = new Servico();
        $prioridade = new Prioridade();
        $servicoUnidade = new ServicoUnidade();
        $cliente = (new Cliente())->setDocumento('1234567890');

        $this
            ->servicoUnidadeRepository
            ->expects($this->once())
            ->method('get')
            ->with($unidade, $servico)
            ->willReturn($servicoUnidade);

        $this
            ->clienteRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['documento' => $cliente->getDocumento()])
            ->willReturn($cliente);

        $this
            ->storage
            ->expects($this->once())
            ->method('distribui')
            ->with(
                $this->isInstanceOf(Atendimento::class),
                $this->isNull(), // agendamento is null
            )
            ->willReturnCallback(fn (Atendimento $atendimento) => $atendimento->setId(1));

        $atendimento = $this->service->distribuiSenha(
            $unidade,
            $usuario,
            $servico,
            $prioridade,
            $cliente
        );

        $this->assertNotNull($atendimento->getId());
        $this->assertSame($cliente, $atendimento->getCliente());
    }

    public function testDistribuiSenhaWithAppointment(): void
    {
        $unidade = new Unidade();
        $usuario = (new Usuario())->setAdmin(true);
        $servico = new Servico();
        $prioridade = new Prioridade();
        $servicoUnidade = new ServicoUnidade();

        $agendamento = (new Agendamento())
            ->setData($this->clock->now())
            ->setHora($this->clock->now())
            ->setCliente(new Cliente());

        $this
            ->servicoUnidadeRepository
            ->expects($this->once())
            ->method('get')
            ->with($unidade, $servico)
            ->willReturn($servicoUnidade);

        $this
            ->storage
            ->expects($this->once())
            ->method('distribui')
            ->with(
                $this->isInstanceOf(Atendimento::class),
                $this->equalTo($agendamento),
            )
            ->willReturnCallback(fn (Atendimento $atendimento) => $atendimento->setId(1));

        $atendimento = $this->service->distribuiSenha(
            $unidade,
            $usuario,
            $servico,
            $prioridade,
            new Cliente(),
            $agendamento,
        );

        $this->assertNotNull($atendimento->getId());
        $this->assertSame($agendamento->getCliente(), $atendimento->getCliente());
    }

    private function buildAtendimento(): Atendimento
    {
        $servico = (new Servico())->setNome('Service 1');
        $unidade = (new Unidade())->setNome('Unity 1');
        $atendimento = (new Atendimento())
            ->setUnidade($unidade)
            ->setServico($servico)
            ->setNumeroLocal(21);
        $atendimento->setLocal(
            (new Local())->setNome('Place 1')
        );
        $atendimento->setPrioridade(
            (new Prioridade())
                ->setNome('Normal')
                ->setPeso(1)
        );
        $atendimento->setCliente(
            (new Cliente())
                ->setNome('Customer 1')
                ->setDocumento('1234567890')
        );
        $atendimento->setSenha(
            (new Senha())
                ->setSigla('ABC')
                ->setNumero(100)
        );

        return $atendimento;
    }
}
