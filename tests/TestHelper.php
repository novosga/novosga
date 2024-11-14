<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use App\Entity\Contador;
use App\Entity\Lotacao;
use App\Entity\Perfil;
use App\Entity\Prioridade;
use App\Entity\Servico;
use App\Entity\ServicoUnidade;
use App\Entity\ServicoUsuario;
use App\Entity\Unidade;
use App\Entity\Usuario;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use League\Bundle\OAuth2ServerBundle\Entity\AccessToken as AccessTokenEntity;
use League\Bundle\OAuth2ServerBundle\Entity\Client as ClientEntity;
use League\Bundle\OAuth2ServerBundle\Entity\Scope as ScopeEntity;
use League\Bundle\OAuth2ServerBundle\Manager\AccessTokenManagerInterface;
use League\OAuth2\Server\CryptKey;
use Novosga\Entity\LotacaoInterface;
use Novosga\Entity\PerfilInterface;
use Novosga\Entity\PrioridadeInterface;
use Novosga\Entity\ServicoInterface;
use Novosga\Entity\ServicoUnidadeInterface;
use Novosga\Entity\ServicoUsuarioInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Entity\UsuarioInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class TestHelper
{
    private function __construct()
    {
    }

    public static function removeTestData(EntityManagerInterface $em): void
    {
        $em->getConnection()->executeQuery('DELETE FROM contador');
        $em->getConnection()->executeQuery('DELETE FROM painel_senha');
        $em->getConnection()->executeQuery('DELETE FROM servicos_unidades');
        $em->getConnection()->executeQuery('DELETE FROM servicos_usuarios');
        $em->getConnection()->executeQuery('DELETE FROM lotacoes');
        $em->getConnection()->executeQuery('DELETE FROM perfis');
        $em->getConnection()->executeQuery('DELETE FROM atendimentos');
        $em->getConnection()->executeQuery('DELETE FROM prioridades');
        $em->getConnection()->executeQuery('DELETE FROM servicos');
        $em->getConnection()->executeQuery('DELETE FROM unidades');
    }

    public static function getUser(EntityManagerInterface $em): UsuarioInterface
    {
        return $em->getRepository(Usuario::class)->findOneBy(['login' => AppFixtures::USER_USERNAME]);
    }

    public static function createUnidade(EntityManagerInterface $em, string $name = 'Test'): UnidadeInterface
    {
        $unidade = (new Unidade())
            ->setNome(sprintf('%s %s ', $name, time()))
            ->setDescricao('test')
            ->setAtivo(true);

        $em->persist($unidade);
        $em->flush();

        return $unidade;
    }

    public static function createServico(EntityManagerInterface $em, string $name = 'Test'): ServicoInterface
    {
        $servico = (new Servico())
            ->setNome(sprintf('%s %s ', $name, time()))
            ->setDescricao('test')
            ->setAtivo(true)
            ->setPeso(0);

        $em->persist($servico);
        $em->flush();

        return $servico;
    }

    public static function createPrioridade(
        EntityManagerInterface $em,
        string $name = 'Test',
        int $peso = 0,
    ): PrioridadeInterface {
        $prioridade = (new Prioridade())
            ->setNome($name)
            ->setDescricao('test')
            ->setPeso($peso);

        $em->persist($prioridade);
        $em->flush();

        return $prioridade;
    }

    /** @param string[] $modulos */
    public static function createPerfil(
        EntityManagerInterface $em,
        string $name = 'Test',
        array $modulos = [],
    ): PerfilInterface {
        $perfil = (new Perfil())
            ->setNome($name)
            ->setDescricao('test')
            ->setModulos($modulos);

        $em->persist($perfil);
        $em->flush();

        return $perfil;
    }

    public static function generateJwtToken(ContainerInterface $container): string
    {
        /** @var ParameterBagInterface */
        $parameters = $container->get(ParameterBagInterface::class);
        $privateKey = $parameters->get('private_key');
        $passphrase = $parameters->get('passphrase');

        /** @var AccessTokenManagerInterface */
        $tokenManager = $container->get(AccessTokenManagerInterface::class);
        $accessToken = $tokenManager->find(AppFixtures::OAUTH2_ACCESS_TOKEN_ID);

        $clientEntity = new ClientEntity();
        $clientEntity->setIdentifier($accessToken->getClient()->getIdentifier());
        $clientEntity->setRedirectUri(array_map('strval', $accessToken->getClient()->getRedirectUris()));

        $accessTokenEntity = new AccessTokenEntity();
        $accessTokenEntity->setPrivateKey(new CryptKey($privateKey, $passphrase, false));
        $accessTokenEntity->setIdentifier($accessToken->getIdentifier());
        $accessTokenEntity->setExpiryDateTime(DateTimeImmutable::createFromInterface($accessToken->getExpiry()));
        $accessTokenEntity->setClient($clientEntity);
        $accessTokenEntity->setUserIdentifier((string) $accessToken->getUserIdentifier());

        foreach ($accessToken->getScopes() as $scope) {
            $scopeEntity = new ScopeEntity();
            $scopeEntity->setIdentifier((string) $scope);

            $accessTokenEntity->addScope($scopeEntity);
        }

        return $accessTokenEntity->toString();
    }

    public static function linkServicoUnidade(
        EntityManagerInterface $em,
        ServicoInterface $servico,
        UnidadeInterface $unidade,
        string $sigla = 'A',
        int $peso = 0,
    ): ServicoUnidadeInterface {
        $su = (new ServicoUnidade())
            ->setUnidade($unidade)
            ->setServico($servico)
            ->setSigla($sigla)
            ->setPeso($peso);

        $counter = (new Contador())
            ->setUnidade($unidade)
            ->setServico($servico)
            ->setNumero(0);

        $em->persist($su);
        $em->persist($counter);
        $em->flush();

        return $su;
    }

    public static function linkServicoUsuario(
        EntityManagerInterface $em,
        ServicoInterface $servico,
        UnidadeInterface $unidade,
        UsuarioInterface $usuario,
        int $peso = 0,
    ): ServicoUsuarioInterface {
        $su = (new ServicoUsuario())
            ->setUnidade($unidade)
            ->setUsuario($usuario)
            ->setServico($servico)
            ->setPeso($peso);

        $em->persist($su);
        $em->flush();

        return $su;
    }

    public static function linkUnidadeUsuario(
        EntityManagerInterface $em,
        UnidadeInterface $unidade,
        UsuarioInterface $usuario,
        PerfilInterface $perfil,
    ): LotacaoInterface {
        $lotacao = (new Lotacao())
            ->setUnidade($unidade)
            ->setUsuario($usuario)
            ->setPerfil($perfil);

        $em->persist($lotacao);
        $em->flush();

        return $lotacao;
    }
}
