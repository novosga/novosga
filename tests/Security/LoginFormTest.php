<?php

namespace App\Tests\Security;

use App\DataFixtures\AppFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class LoginFormTest extends WebTestCase
{
    public function testSecuredIndexWithoutCredentials(): void
    {
        static::createClient()->request('GET', '/');

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseHeaderSame('Location', '/login');
    }

    public function testSecuredIndexWhileLoggedIn(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login');
        $client->submitForm('Entrar', [
            'username' => AppFixtures::USER_USERNAME,
            'password' => AppFixtures::USER_PASSWORD,
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseHeaderSame('Location', '/');

        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains('Bem-vindo');
    }

    public function testSecuredIndexWithWrongCredentials(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login');
        $client->submitForm('Entrar', [
            'username' => 'invalid_username',
            'password' => 'invalid_password',
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseHeaderSame('Location', '/login');

        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('div.alert-danger', 'Credenciais inválidas.');
    }

    public function testSecuredIndexWithDisabledUser(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login');
        $client->submitForm('Entrar', [
            'username' => AppFixtures::DISABLED_USER_USERNAME,
            'password' => AppFixtures::DISABLED_USER_PASSWORD,
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseHeaderSame('Location', '/login');

        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('div.alert-danger', 'Conta desativada.');
    }
}
