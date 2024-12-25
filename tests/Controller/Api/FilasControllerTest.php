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

use App\Entity\Atendimento;
use App\Service\AtendimentoService;
use App\Tests\TestHelper;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Novosga\Entity\PrioridadeInterface;
use Novosga\Entity\ServicoInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Entity\UsuarioInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * FilasControllerTest
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class FilasControllerTest extends WebTestCase
{
    private const TEST_SIGLA = 'ABC';
    private const TEST_PRIORIDADE_NORMAL = 'normal';
    private const TEST_PRIORIDADE_PRIORIDADE = 'prioridade';

    private ?EntityManagerInterface $em = null;
    private ?PrioridadeInterface $prioridadePrioridade = null;
    private ?PrioridadeInterface $prioridadeNormal = null;

    protected function setUp(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);

        TestHelper::removeTestData($this->em);

        $this->prioridadePrioridade = TestHelper::createPrioridade(
            $this->em,
            self::TEST_PRIORIDADE_PRIORIDADE,
            peso: 1
        );
        $this->prioridadeNormal = TestHelper::createPrioridade(
            $this->em,
            self::TEST_PRIORIDADE_NORMAL,
            peso: 0,
        );
    }

    public function testGetFilaUnidadeWithoutAccessToken(): void
    {
        $client = static::getClient();
        $unidade = TestHelper::createUnidade($this->em);

        $url = sprintf('/api/filas/%s', $unidade->getId());
        $client->request('GET', $url);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testGetFilaUnidade(): void
    {
        $client = static::getClient();
        $container = static::getContainer();

        $accessToken = TestHelper::generateJwtToken($container);
        $usuario = TestHelper::getUser($this->em);
        $servico = TestHelper::createServico($this->em);

        $unidade1 = TestHelper::createUnidade($this->em);
        $this->createLinks($servico, $unidade1, $usuario);

        $unidade2 = TestHelper::createUnidade($this->em);
        $this->createLinks($servico, $unidade2, $usuario);

        $unidade3 = TestHelper::createUnidade($this->em);
        $this->createLinks($servico, $unidade3, $usuario);

        // generating tickets for unidade1 and unidade3 that should be ignored
        $this->generateAtendimento(
            sigla: 'ABC',
            numero: 1,
            unidade: $unidade1,
            servico: $servico,
            prioridade: $this->prioridadeNormal,
            dataChegada: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2024-12-17 09:00:00'),
            usuarioTriagem: $usuario,
        );
        $this->generateAtendimento(
            sigla: 'ABC',
            numero: 1,
            unidade: $unidade3,
            servico: $servico,
            prioridade: $this->prioridadeNormal,
            dataChegada: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2024-12-17 09:00:00'),
            usuarioTriagem: $usuario,
        );

        // generating canceled ticket for unidade2 that should be ignored
        $this->generateAtendimento(
            sigla: 'ABC',
            numero: 1,
            unidade: $unidade2,
            servico: $servico,
            prioridade: $this->prioridadeNormal,
            dataChegada: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2024-12-17 08:40:00'),
            usuarioTriagem: $usuario,
            status: AtendimentoService::SENHA_CANCELADA,
        );

        // generating the expected queue (unidade2)
        $senha1Normal = $this->generateAtendimento(
            sigla: 'ABC',
            numero: 1,
            unidade: $unidade2,
            servico: $servico,
            prioridade: $this->prioridadeNormal,
            dataChegada: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2024-12-17 09:00:00'),
            usuarioTriagem: $usuario,
        );
        $senha2Normal = $this->generateAtendimento(
            sigla: 'ABC',
            numero: 2,
            unidade: $unidade2,
            servico: $servico,
            prioridade: $this->prioridadeNormal,
            dataChegada: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2024-12-17 09:03:00'),
            usuarioTriagem: $usuario,
        );
        $senha3Prioridade = $this->generateAtendimento(
            sigla: 'ABC',
            numero: 2,
            unidade: $unidade2,
            servico: $servico,
            prioridade: $this->prioridadePrioridade,
            dataChegada: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2024-12-17 09:05:00'),
            usuarioTriagem: $usuario,
        );

        $url = sprintf('/api/filas/%s', $unidade2->getId());
        $client->request('GET', $url, server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseStatusCodeSame(200);

        $response = $client->getResponse();
        $result = json_decode($response->getContent(), true);

        // assert queue ordering
        $this->assertCount(3, $result);
        $this->assertSame($senha3Prioridade->getId(), $result[0]['id']);
        $this->assertSame($senha1Normal->getId(), $result[1]['id']);
        $this->assertSame($senha2Normal->getId(), $result[2]['id']);
    }

    private function generateAtendimento(
        string $sigla,
        int $numero,
        UnidadeInterface $unidade,
        ServicoInterface $servico,
        PrioridadeInterface $prioridade,
        DateTimeInterface $dataChegada,
        UsuarioInterface $usuarioTriagem,
        string $status = AtendimentoService::SENHA_EMITIDA,
    ): Atendimento {
        $atendimento = (new Atendimento())
            ->setUnidade($unidade)
            ->setServico($servico)
            ->setPrioridade($prioridade)
            ->setDataChegada($dataChegada)
            ->setUsuarioTriagem($usuarioTriagem)
            ->setStatus($status);

        $atendimento->getSenha()->setSigla($sigla);
        $atendimento->getSenha()->setNumero($numero);

        $this->em->persist($atendimento);
        $this->em->flush();

        return $atendimento;
    }

    private function createLinks(
        ServicoInterface $servico,
        UnidadeInterface $unidade,
        UsuarioInterface $usuario,
    ): void {
        TestHelper::linkServicoUnidade($this->em, $servico, $unidade, self::TEST_SIGLA);
        TestHelper::linkServicoUsuario($this->em, $servico, $unidade, $usuario);
    }
}
