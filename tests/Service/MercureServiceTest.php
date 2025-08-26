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

namespace App\Tests\Service;

use App\Entity\Atendimento;
use App\Entity\Unidade;
use App\Entity\Usuario;
use App\Service\MercureService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

/**
 * MercureServiceTest
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class MercureServiceTest extends TestCase
{
    private HubInterface&MockObject $hub;
    private LoggerInterface&MockObject $logger;
    private MercureService $service;

    protected function setUp(): void
    {
        $this->hub = $this->createMock(HubInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->service = new MercureService($this->hub, $this->logger);
    }

    public function testNotificaFilaUnidadeAddsTypeField(): void
    {
        $unidade = $this->createMock(Unidade::class);
        $unidade->method('getId')->willReturn(123);

        $this->hub->expects($this->exactly(2))
            ->method('publish')
            ->with($this->callback(function (Update $update) {
                $data = json_decode($update->getData(), true);
                $topics = $update->getTopics();
                
                // Check the unit-specific topic message
                if (in_array('/unidades/123/fila', $topics)) {
                    $this->assertArrayHasKey('@type', $data);
                    $this->assertEquals('queue.unit', $data['@type']);
                    $this->assertArrayHasKey('id', $data);
                    $this->assertEquals(123, $data['id']);
                }
                
                // Check the general queue topic message
                if (in_array('/fila', $topics)) {
                    $this->assertArrayHasKey('@type', $data);
                    $this->assertEquals('queue.general', $data['@type']);
                }
                
                return true;
            }));

        $this->service->notificaFilaUnidade($unidade);
    }

    public function testNotificaFilaUsuarioAddsTypeField(): void
    {
        $usuario = $this->createMock(Usuario::class);
        $usuario->method('getId')->willReturn(456);

        $this->hub->expects($this->once())
            ->method('publish')
            ->with($this->callback(function (Update $update) {
                $data = json_decode($update->getData(), true);
                $topics = $update->getTopics();
                
                $this->assertContains('/usuarios/456/fila', $topics);
                $this->assertArrayHasKey('@type', $data);
                $this->assertEquals('queue.user', $data['@type']);
                $this->assertArrayHasKey('id', $data);
                $this->assertEquals(456, $data['id']);
                
                return true;
            }));

        $this->service->notificaFilaUsuario($usuario);
    }

    public function testNotificaPainelAddsTypeField(): void
    {
        $atendimento = $this->createMock(Atendimento::class);
        $atendimento->method('getId')->willReturn(789);
        
        $unidade = $this->createMock(Unidade::class);
        $unidade->method('getId')->willReturn(123);
        $atendimento->method('getUnidade')->willReturn($unidade);

        $this->hub->expects($this->once())
            ->method('publish')
            ->with($this->callback(function (Update $update) {
                $data = json_decode($update->getData(), true);
                $topics = $update->getTopics();
                
                $this->assertContains('/paineis', $topics);
                $this->assertContains('/unidades/123/painel', $topics);
                $this->assertArrayHasKey('@type', $data);
                $this->assertEquals('panel.general', $data['@type']);
                $this->assertArrayHasKey('id', $data);
                $this->assertEquals(789, $data['id']);
                
                return true;
            }));

        $this->service->notificaPainel($atendimento);
    }

    public function testNotificaAtendimentoAddsTypeField(): void
    {
        $atendimento = $this->createMock(Atendimento::class);
        $atendimento->method('getId')->willReturn(999);
        
        $unidade = $this->createMock(Unidade::class);
        $unidade->method('getId')->willReturn(123);
        $atendimento->method('getUnidade')->willReturn($unidade);

        $this->hub->expects($this->once())
            ->method('publish')
            ->with($this->callback(function (Update $update) {
                $data = json_decode($update->getData(), true);
                $topics = $update->getTopics();
                
                $this->assertContains('/atendimentos/999', $topics);
                $this->assertContains('/unidades/123/fila', $topics);
                $this->assertArrayHasKey('@type', $data);
                $this->assertEquals('attendance', $data['@type']);
                $this->assertArrayHasKey('id', $data);
                $this->assertEquals(999, $data['id']);
                
                return true;
            }));

        $this->service->notificaAtendimento($atendimento);
    }

    public function testNotificaFilaUnidadeWithNullUnidade(): void
    {
        $this->hub->expects($this->once())
            ->method('publish')
            ->with($this->callback(function (Update $update) {
                $data = json_decode($update->getData(), true);
                $topics = $update->getTopics();
                
                $this->assertContains('/fila', $topics);
                $this->assertArrayHasKey('@type', $data);
                $this->assertEquals('queue.general', $data['@type']);
                
                return true;
            }));

        $this->service->notificaFilaUnidade(null);
    }
}