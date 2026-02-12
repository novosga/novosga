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

use App\Tests\TestHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * ClientesControllerTest
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class ClientesControllerTest extends WebTestCase
{
    private ?EntityManagerInterface $em = null;

    protected function setUp(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);

        TestHelper::removeTestData($this->em);
    }

    public function testGetClientesWithoutAccessToken(): void
    {
        $client = static::getClient();

        $client->request('GET', '/api/clientes');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testGetClientesWithInvalidAccessToken(): void
    {
        $client = static::getClient();

        $client->request('GET', '/api/clientes', server: [
            'HTTP_AUTHORIZATION' => 'Bearer test',
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetClientesWithValidAccessToken(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $clientes = [
            TestHelper::createCliente($this->em, 'Cliente 1', '12345678901'),
            TestHelper::createCliente($this->em, 'Cliente 2', '12345678902'),
            TestHelper::createCliente($this->em, 'Cliente 3', '12345678903'),
        ];

        $client->request('GET', '/api/clientes', server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseStatusCodeSame(200);

        $response = $client->getResponse();
        $result = json_decode($response->getContent(), true);
        $this->assertSameSize($clientes, $result);

        for ($i = 0; $i < count($clientes); $i++) {
            $fromDb = $clientes[$i];
            $fromApi = $result[$i];

            $this->assertSame($fromDb->getId(), $fromApi['id']);
            $this->assertSame($fromDb->getNome(), $fromApi['nome']);
            $this->assertSame($fromDb->getDocumento(), $fromApi['documento']);
            $this->assertSame($fromDb->getEmail(), $fromApi['email']);
            $this->assertSame($fromDb->getTelefone(), $fromApi['telefone']);
        }
    }

    public function testGetClienteByInvalidId(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $client->request('GET', '/api/clientes/999', server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetClienteByValidId(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $cliente = TestHelper::createCliente($this->em, 'Test Cliente', '12345678900');

        $client->request('GET', sprintf('/api/clientes/%s', $cliente->getId()), server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertSame($cliente->getId(), $result['id']);
        $this->assertSame($cliente->getNome(), $result['nome']);
        $this->assertSame($cliente->getDocumento(), $result['documento']);
        $this->assertSame($cliente->getEmail(), $result['email']);
        $this->assertSame($cliente->getTelefone(), $result['telefone']);
    }
}
