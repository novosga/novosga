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

use App\Tests\TestHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * ServicosControllerTest
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class ServicosControllerTest extends WebTestCase
{
    private ?EntityManagerInterface $em = null;

    protected function setUp(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);

        TestHelper::removeTestData($this->em);
    }

    public function testGetServicosWithoutAccessToken(): void
    {
        $client = static::getClient();

        $client->request('GET', '/api/servicos');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testGetServicosWithInvalidAccessToken(): void
    {
        $client = static::getClient();

        $client->request('GET', '/api/servicos', server: [
            'HTTP_AUTHORIZATION' => 'Bearer test',
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetServicosWithValidAccessToken(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $servicos = [
            TestHelper::createServico($this->em),
            TestHelper::createServico($this->em),
            TestHelper::createServico($this->em),
        ];

        $client->request('GET', '/api/servicos', server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseStatusCodeSame(200);

        $response = $client->getResponse();
        $result = json_decode($response->getContent(), true);
        $this->assertSameSize($servicos, $result);

        for ($i = 0; $i < count($servicos); $i++) {
            $fromDb = $servicos[$i];
            $fromApi = $result[$i];

            $this->assertSame($fromDb->getId(), $fromApi['id']);
            $this->assertSame($fromDb->getNome(), $fromApi['nome']);
            $this->assertSame($fromDb->getDescricao(), $fromApi['descricao']);
            $this->assertSame($fromDb->getPeso(), $fromApi['peso']);
            $this->assertSame($fromDb->isAtivo(), $fromApi['ativo']);
        }
    }

    public function testGetServicoByInvalidId(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $client->request('GET', '/api/servicos/999', server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetServicoByValidId(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $servico = TestHelper::createServico($this->em);

        $client->request('GET', sprintf('/api/servicos/%s', $servico->getId()), server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertSame($servico->getId(), $result['id']);
        $this->assertSame($servico->getNome(), $result['nome']);
        $this->assertSame($servico->getDescricao(), $result['descricao']);
        $this->assertSame($servico->getPeso(), $result['peso']);
        $this->assertSame($servico->isAtivo(), $result['ativo']);
    }
}
