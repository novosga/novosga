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

use App\DataFixtures\AppFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OAuthTokenTest extends WebTestCase
{
    public function testAccessTokenEndpointWithGet(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/token');

        $this->assertResponseStatusCodeSame(405);
    }

    public function testAccessTokenWithWrongClient(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/token', [
            'grant_type' => 'password',
            'client_id' => 'test',
            'client_secret' => 'test',
            'username' => AppFixtures::USER_USERNAME,
            'password' => AppFixtures::USER_PASSWORD,
        ]);

        $this->assertResponseStatusCodeSame(401);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            [
                'error' => 'invalid_client',
                'error_description' => 'Client authentication failed',
            ],
            $data,
        );
    }

    public function testAccessTokenWithWrongUser(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/token', [
            'grant_type' => 'password',
            'client_id' => AppFixtures::OAUTH2_CLIENT_ID,
            'client_secret' => AppFixtures::OAUTH2_CLIENT_SECRET,
            'username' => 'test',
            'password' => 'test',
        ]);

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(
            [
                'error' => 'invalid_grant',
                'error_description' => 'The user credentials were incorrect.',
            ],
            $data,
        );
    }

    public function testAccessToken(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/token', [
            'grant_type' => 'password',
            'client_id' => AppFixtures::OAUTH2_CLIENT_ID,
            'client_secret' => AppFixtures::OAUTH2_CLIENT_SECRET,
            'username' => AppFixtures::USER_USERNAME,
            'password' => AppFixtures::USER_PASSWORD,
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

        $client->request('POST', '/api/token', [
            'grant_type' => 'password',
            'client_id' => AppFixtures::OAUTH2_CLIENT_ID,
            'client_secret' => AppFixtures::OAUTH2_CLIENT_SECRET,
            'username' => AppFixtures::USER_USERNAME,
            'password' => AppFixtures::USER_PASSWORD,
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('access_token', $data);

        $client->request('GET', '/api', server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $data['access_token']),
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('status', $data);
        $this->assertSame('ok', $data['status']);
        $this->assertArrayHasKey('time', $data);
        $this->assertArrayHasKey('mercureUrl', $data);
    }
}
