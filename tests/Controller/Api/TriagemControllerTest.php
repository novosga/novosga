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

namespace App\Tests\Controller\Api;

use App\Service\AtendimentoService;
use App\Tests\TestHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TriagemControllerTest extends WebTestCase
{
    private ?EntityManagerInterface $em = null;

    protected function setUp(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);

        TestHelper::removeTestData($this->em);
    }

    public function testDistribuiSenhaWithoutAccessToken(): void
    {
        $client = static::getClient();

        $client->request('POST', '/api/distribui');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDistribuiSenhaWithInvalidAccessToken(): void
    {
        $client = static::getClient();

        $client->request('POST', '/api/distribui', server: [
            'HTTP_AUTHORIZATION' => 'Bearer test',
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testDistribuiSenhaWithoutPayload(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $client->jsonRequest('POST', '/api/distribui', server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testDistribuiSenhaWithEmptyPayload(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $client->jsonRequest('POST', '/api/distribui', parameters: [], server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseStatusCodeSame(422);
        $response = $client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertIsArray($result['error']);
        $this->assertEquals($result['error'], [
            'unidade' => 'Este valor não deve ser nulo.',
            'prioridade' => 'Este valor não deve ser nulo.',
            'servico' => 'Este valor não deve ser nulo.',
        ]);
    }

    public function testDistribuiSenhaWithWrongUnidadeId(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());
        $servico = TestHelper::createServico($this->em);
        $prioridade = TestHelper::createPrioridade($this->em);

        $data = [
            'unidade' => 1,
            'servico' => $servico->getId(),
            'prioridade' => $prioridade->getId(),
        ];

        $client->jsonRequest('POST', '/api/distribui', parameters: $data, server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseStatusCodeSame(422);
        $response = $client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame($result['error'], 'Unidade inválida');
    }

    public function testDistribuiSenhaWithWrongServicoId(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());
        $unidade = TestHelper::createUnidade($this->em);
        $prioridade = TestHelper::createPrioridade($this->em);

        $data = [
            'unidade' => $unidade->getId(),
            'servico' => 1,
            'prioridade' => $prioridade->getId(),
        ];

        $client->jsonRequest('POST', '/api/distribui', parameters: $data, server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseStatusCodeSame(422);
        $response = $client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame($result['error'], 'Serviço inválido');
    }

    public function testDistribuiSenhaWithWrongPrioridadeId(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());
        $unidade = TestHelper::createUnidade($this->em);
        $servico = TestHelper::createServico($this->em);

        $data = [
            'unidade' => $unidade->getId(),
            'servico' => $servico->getId(),
            'prioridade' => 1,
        ];

        $client->jsonRequest('POST', '/api/distribui', parameters: $data, server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseStatusCodeSame(422);
        $response = $client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame($result['error'], 'Prioridade inválida');
    }

    public function testDistribuiSenhaWithoutLotacaoOnUnidade(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());
        $unidade = TestHelper::createUnidade($this->em);
        $servico = TestHelper::createServico($this->em);
        $prioridade = TestHelper::createPrioridade($this->em);

        $data = [
            'unidade' => $unidade->getId(),
            'servico' => $servico->getId(),
            'prioridade' => $prioridade->getId(),
        ];

        $client->jsonRequest('POST', '/api/distribui', parameters: $data, server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseStatusCodeSame(422);
        $response = $client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame(
            $result['error'],
            'O usuário que está tentando distribuir senha não tem lotação na unidade escolhida.',
        );
    }

    public function testDistribuiSenhaWhenServicoIsNotAvailableOnUnidade(): void
    {
        $client = static::getClient();
        $usuario = TestHelper::getUser($this->em);
        $accessToken = TestHelper::generateJwtToken(static::getContainer());
        $unidade = TestHelper::createUnidade($this->em);
        $servico = TestHelper::createServico($this->em);
        $prioridade = TestHelper::createPrioridade($this->em);
        $perfil = TestHelper::createPerfil($this->em);

        TestHelper::linkUnidadeUsuario($this->em, $unidade, $usuario, $perfil);

        $data = [
            'unidade' => $unidade->getId(),
            'servico' => $servico->getId(),
            'prioridade' => $prioridade->getId(),
        ];

        $client->jsonRequest('POST', '/api/distribui', parameters: $data, server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseStatusCodeSame(422);
        $response = $client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame($result['error'], 'Serviço não disponível para a unidade atual');
    }

    public function testDistribuiSenha(): void
    {
        $client = static::getClient();
        $usuario = TestHelper::getUser($this->em);
        $accessToken = TestHelper::generateJwtToken(static::getContainer());
        $unidade = TestHelper::createUnidade($this->em);
        $servico = TestHelper::createServico($this->em);
        $prioridade = TestHelper::createPrioridade($this->em);
        $perfil = TestHelper::createPerfil($this->em);

        TestHelper::linkUnidadeUsuario($this->em, $unidade, $usuario, $perfil);
        $servicoUnidade = TestHelper::linkServicoUnidade($this->em, $servico, $unidade);

        $data = [
            'unidade' => $unidade->getId(),
            'servico' => $servico->getId(),
            'prioridade' => $prioridade->getId(),
        ];

        $client->jsonRequest('POST', '/api/distribui', parameters: $data, server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseStatusCodeSame(201);
        $response = $client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertSame($result['status'], AtendimentoService::SENHA_EMITIDA);
        $this->assertSame($result['senha']['sigla'], $servicoUnidade->getSigla());
        $this->assertSame($result['servico']['id'], $servico->getId());
        $this->assertSame($result['prioridade']['id'], $prioridade->getId());
    }
}
