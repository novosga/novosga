<?php

namespace App\DataFixtures;

use App\Entity\Usuario;
use DateInterval;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use League\Bundle\OAuth2ServerBundle\Manager\AccessTokenManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\ClientManagerInterface;
use League\Bundle\OAuth2ServerBundle\Model\AccessToken;
use League\Bundle\OAuth2ServerBundle\Model\Client;
use League\Bundle\OAuth2ServerBundle\ValueObject\Grant;
use League\Bundle\OAuth2ServerBundle\ValueObject\Scope;
use Psr\Clock\ClockInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public const USER_USERNAME = 'test';
    public const USER_PASSWORD = '123456';
    public const OAUTH2_CLIENT_NAME = 'test_oauth2_client_name';
    public const OAUTH2_CLIENT_ID = 'test_oauth2_client_id';
    public const OAUTH2_CLIENT_SECRET = 'test_oauth2_client_secret';
    public const OAUTH2_ACCESS_TOKEN_ID = 'test_oauth2_access_token_id';

    public function __construct(
        private readonly ClockInterface $clock,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ClientManagerInterface $oauthClientManager,
        private readonly AccessTokenManagerInterface $oauthTokenManager,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user = (new Usuario())
            ->setNome('test')
            ->setSobrenome('test')
            ->setLogin(self::USER_USERNAME);

        $password = $this->passwordHasher->hashPassword($user, self::USER_PASSWORD);
        $user->setSenha($password);

        $manager->persist($user);
        $manager->flush();

        // OAuth2
        $client = new Client(
            name: self::OAUTH2_CLIENT_NAME,
            identifier: self::OAUTH2_CLIENT_ID,
            secret: self::OAUTH2_CLIENT_SECRET,
        );
        $client->setGrants(new Grant('token'), new Grant('password'), new Grant('refresh_token'));

        $this->oauthClientManager->save($client);

        $token = new AccessToken(
            identifier: self::OAUTH2_ACCESS_TOKEN_ID,
            expiry: $this->clock->now()->add(new DateInterval('P365D')),
            client: $client,
            userIdentifier: self::USER_USERNAME,
            scopes: [new Scope('email')],
        );

        $this->oauthTokenManager->save($token);
    }
}
