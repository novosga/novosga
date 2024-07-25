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

use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\ClientManagerInterface;
use League\Bundle\OAuth2ServerBundle\Model\Client;
use League\Bundle\OAuth2ServerBundle\ValueObject\Grant;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class OAuthTokenTest extends WebTestCase
{
    private const TEST_USER_PASSWORD = 'test_123456';

    public function testAccessTokenEndpointWithGet(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/token');

        $this->assertResponseStatusCodeSame(405);
    }

    public function testAccessTokenWithWrongClient(): void
    {
        $client = static::createClient();

        // generating application user
        $user = $this->createTestUser(
            $client->getContainer()->get(EntityManagerInterface::class),
            $client->getContainer()->get(UserPasswordHasherInterface::class),
        );

        $client->request('POST', '/api/token', [
            'grant_type' => 'password',
            'client_id' => 'test',
            'client_secret' => 'test',
            'username' => $user->getLogin(),
            'password' => self::TEST_USER_PASSWORD,
        ]);

        $this->assertResponseStatusCodeSame(401);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            [
                'error' => 'invalid_client',
                'error_description' => 'Client authentication failed',
                'message' => "Client authentication failed",
            ],
            $data,
        );
    }

    public function testAccessTokenWithWrongUser(): void
    {
        $client = static::createClient();

        // generating oauth client
        $oauthClientManager = $client->getContainer()->get(ClientManagerInterface::class);
        $oauthClient = $this->createOauthClient($oauthClientManager);

        $client->request('POST', '/api/token', [
            'grant_type' => 'password',
            'client_id' => $oauthClient->getIdentifier(),
            'client_secret' => $oauthClient->getSecret(),
            'username' => 'test',
            'password' => 'test',
        ]);

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            [
                'error' => 'invalid_grant',
                'error_description' => 'The user credentials were incorrect.',
                'message' => "The user credentials were incorrect.",
            ],
            $data,
        );
    }

    public function testAccessToken(): void
    {
        $client = static::createClient();

        // generating application user
        $user = $this->createTestUser(
            $client->getContainer()->get(EntityManagerInterface::class),
            $client->getContainer()->get(UserPasswordHasherInterface::class),
        );

        // generating oauth client
        $oauthClientManager = $client->getContainer()->get(ClientManagerInterface::class);
        $oauthClient = $this->createOauthClient($oauthClientManager);

        $client->request('POST', '/api/token', [
            'grant_type' => 'password',
            'client_id' => $oauthClient->getIdentifier(),
            'client_secret' => $oauthClient->getSecret(),
            'username' => $user->getLogin(),
            'password' => self::TEST_USER_PASSWORD,
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('access_token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertArrayHasKey('expires_in', $data);
    }

    public function testProtectedEndpointWithoutAccessToken(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testProtectedEndpointWithWrongAccessToken(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api', server: [
            'HTTP_AUTHORIZATION' => 'Bearer testToken',
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testProtectedEndpointWithValidAccessToken(): void
    {
        $client = static::createClient();

        // generating application user
        $user = $this->createTestUser(
            $client->getContainer()->get(EntityManagerInterface::class),
            $client->getContainer()->get(UserPasswordHasherInterface::class),
        );

        // generating oauth client
        $oauthClientManager = $client->getContainer()->get(ClientManagerInterface::class);
        $oauthClient = $this->createOauthClient($oauthClientManager);

        $client->request('POST', '/api/token', [
            'grant_type' => 'password',
            'client_id' => $oauthClient->getIdentifier(),
            'client_secret' => $oauthClient->getSecret(),
            'username' => $user->getLogin(),
            'password' => self::TEST_USER_PASSWORD,
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('access_token', $data);

        $client->request('GET', '/api', server: [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $data['access_token'],
        ]);

        // {"status":"ok","time":1721845867,"mercureUrl":"http:\/\/127.0.0.1:3000\/.well-known\/mercure"}
        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('status', $data);
        $this->assertSame('ok', $data['status']);
        $this->assertArrayHasKey('time', $data);
        $this->assertArrayHasKey('mercureUrl', $data);
    }

    private function createOauthClient(ClientManagerInterface $clientManager): Client
    {
        $client = new Client(
            name: 'test',
            identifier: hash('md5', random_bytes(16)),
            secret: hash('sha512', random_bytes(32)),
        );
        $client->setGrants(new Grant('token'), new Grant('password'), new Grant('refresh_token'));
        $clientManager->save($client);

        return $client;
    }

    private function createTestUser(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Usuario
    {
        $user = (new Usuario())
            ->setNome('test')
            ->setSobrenome('test')
            ->setLogin(substr('test_' . hash('md5', random_bytes(16)), 0, 30));

        $password = $passwordHasher->hashPassword($user, self::TEST_USER_PASSWORD);
        $user->setSenha($password);

        $em->persist($user);
        $em->flush();

        return $user;
    }
}
