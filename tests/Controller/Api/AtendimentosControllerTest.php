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

namespace App\Tests\Controller\Api;

use App\Entity\Atendimento;
use App\Entity\Senha;
use App\Service\AtendimentoService;
use App\Tests\TestHelper;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Novosga\Entity\LocalInterface;
use Novosga\Entity\PrioridadeInterface;
use Novosga\Entity\ServicoInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Entity\UsuarioInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * AtendimentosControllerTest
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class AtendimentosControllerTest extends WebTestCase
{
    private ?EntityManagerInterface $em = null;

    protected function setUp(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);

        TestHelper::removeTestData($this->em);
    }

    public function testChamarAtendimentoWithoutAccessToken(): void
    {
        $client = static::getClient();
        $atendimento = $this->createAtendimento();

        $url = sprintf('/api/atendimentos/%s/chamar', $atendimento->getId());
        $client->request('POST', $url);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testChamarAtendimentoWithInvalidPayload(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());
        $atendimento = $this->createAtendimento();

        $url = sprintf('/api/atendimentos/%s/chamar', $atendimento->getId());
        $client->jsonRequest('POST', $url, parameters: [], server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testChamarAtendimento(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());
        $atendimento = $this->createAtendimento();
        $local = TestHelper::createLocal($this->em, 'Guichê 1');

        $data = [
            'local' => $local->getId(),
            'numeroLocal' => 1,
        ];

        $url = sprintf('/api/atendimentos/%s/chamar', $atendimento->getId());
        $client->jsonRequest('POST', $url, parameters: $data, server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseStatusCodeSame(200);
        $response = $client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals($atendimento->getId(), $result['id']);
        $this->assertEquals(AtendimentoService::CHAMADO_PELA_MESA, $result['status']);
    }

    public function testIniciarAtendimentoWithoutAccessToken(): void
    {
        $client = static::getClient();
        $atendimento = $this->createAtendimento();

        $url = sprintf('/api/atendimentos/%s/iniciar', $atendimento->getId());
        $client->request('POST', $url);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testIniciarAtendimento(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());
        $atendimento = $this->createAtendimento();
        $local = TestHelper::createLocal($this->em, 'Guichê 1');
        $usuario = TestHelper::getUser($this->em);

        // First, call the ticket
        $container = static::getContainer();
        $service = $container->get(AtendimentoService::class);
        $service->chamarAtendimento($atendimento, $usuario, $local, 1);

        $url = sprintf('/api/atendimentos/%s/iniciar', $atendimento->getId());
        $client->jsonRequest('POST', $url, server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseStatusCodeSame(200);
        $response = $client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals($atendimento->getId(), $result['id']);
        $this->assertEquals(AtendimentoService::ATENDIMENTO_INICIADO, $result['status']);
    }

    public function testEncerrarAtendimentoWithoutAccessToken(): void
    {
        $client = static::getClient();
        $atendimento = $this->createAtendimento();

        $url = sprintf('/api/atendimentos/%s/encerrar', $atendimento->getId());
        $client->request('POST', $url);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testEncerrarAtendimentoWithInvalidPayload(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());
        $atendimento = $this->createAtendimento();

        $url = sprintf('/api/atendimentos/%s/encerrar', $atendimento->getId());
        $client->jsonRequest('POST', $url, parameters: [], server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testEncerrarAtendimento(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());
        $atendimento = $this->createAtendimento();
        $local = TestHelper::createLocal($this->em, 'Guichê 1');
        $usuario = TestHelper::getUser($this->em);

        // First, call and start the ticket
        $container = static::getContainer();
        $service = $container->get(AtendimentoService::class);
        $service->chamarAtendimento($atendimento, $usuario, $local, 1);
        $service->iniciarAtendimento($atendimento, $usuario);

        $data = [
            'servicosRealizados' => [$atendimento->getServico()->getId()],
        ];

        $url = sprintf('/api/atendimentos/%s/encerrar', $atendimento->getId());
        $client->jsonRequest('POST', $url, parameters: $data, server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseStatusCodeSame(200);
        $response = $client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals($atendimento->getId(), $result['id']);
        $this->assertEquals(AtendimentoService::ATENDIMENTO_ENCERRADO, $result['status']);
    }

    private function createAtendimento(): Atendimento
    {
        $unidade = TestHelper::createUnidade($this->em);
        $servico = TestHelper::createServico($this->em);
        $prioridade = TestHelper::createPrioridade($this->em, 'Normal', 0);
        $usuario = TestHelper::getUser($this->em);

        // Link ServicoUnidade
        TestHelper::linkServicoUnidade($this->em, $servico, $unidade, 'A', 0);

        $senha = new Senha();
        $senha->setSigla('A');
        $senha->setNumero(1);

        $atendimento = new Atendimento();
        $atendimento->setUnidade($unidade);
        $atendimento->setServico($servico);
        $atendimento->setPrioridade($prioridade);
        $atendimento->setSenha($senha);
        $atendimento->setUsuarioTriagem($usuario);
        $atendimento->setStatus(AtendimentoService::SENHA_EMITIDA);
        $atendimento->setDataChegada(new DateTimeImmutable());

        $this->em->persist($atendimento);
        $this->em->flush();

        return $atendimento;
    }
}
