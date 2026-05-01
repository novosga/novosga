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
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * AgendamentosControllerTest
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class AgendamentosControllerTest extends WebTestCase
{
    private ?EntityManagerInterface $em = null;

    protected function setUp(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);

        TestHelper::removeTestData($this->em);
    }

    public function testSearchWithoutAccessToken(): void
    {
        $client = static::getClient();

        $client->request('GET', '/api/agendamentos');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testSearchWithInvalidAccessToken(): void
    {
        $client = static::getClient();

        $client->request('GET', '/api/agendamentos', server: [
            'HTTP_AUTHORIZATION' => 'Bearer invalid',
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testSearchReturnsAll(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $unidade = TestHelper::createUnidade($this->em);
        $servico = TestHelper::createServico($this->em);
        $cliente = TestHelper::createCliente($this->em, 'Test Cliente');
        $data = new DateTime('2025-06-01');
        $hora = new DateTime('09:00');

        $agendamentos = [
            TestHelper::createAgendamento($this->em, $cliente, $unidade, $servico, $data, $hora),
            TestHelper::createAgendamento($this->em, $cliente, $unidade, $servico, $data, new DateTime('10:00')),
        ];

        $client->request('GET', '/api/agendamentos', server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseIsSuccessful();

        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(count($agendamentos), $result);
    }

    public function testSearchById(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $unidade = TestHelper::createUnidade($this->em);
        $servico = TestHelper::createServico($this->em);
        $cliente = TestHelper::createCliente($this->em, 'Test Cliente');
        $data = new DateTime('2025-06-01');

        $target = TestHelper::createAgendamento($this->em, $cliente, $unidade, $servico, $data, new DateTime('09:00'));
        TestHelper::createAgendamento($this->em, $cliente, $unidade, $servico, $data, new DateTime('10:00'));

        $client->request('GET', '/api/agendamentos', ['q' => [sprintf('id:%d', $target->getId())]], server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseIsSuccessful();

        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $result);
        $this->assertSame($target->getId(), $result[0]['id']);
    }

    public function testSearchByData(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $unidade = TestHelper::createUnidade($this->em);
        $servico = TestHelper::createServico($this->em);
        $cliente = TestHelper::createCliente($this->em, 'Test Cliente');

        TestHelper::createAgendamento(
            $this->em,
            $cliente,
            $unidade,
            $servico,
            new DateTime('2025-06-01'),
            new DateTime('09:00'),
        );
        TestHelper::createAgendamento(
            $this->em,
            $cliente,
            $unidade,
            $servico,
            new DateTime('2025-06-02'),
            new DateTime('09:00'),
        );
        TestHelper::createAgendamento(
            $this->em,
            $cliente,
            $unidade,
            $servico,
            new DateTime('2025-06-02'),
            new DateTime('10:00'),
        );

        $client->request('GET', '/api/agendamentos', ['q' => ['data:2025-06-02']], server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseIsSuccessful();

        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(2, $result);
    }

    public function testSearchByOid(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $unidade = TestHelper::createUnidade($this->em);
        $servico = TestHelper::createServico($this->em);
        $cliente = TestHelper::createCliente($this->em, 'Test Cliente');
        $data = new DateTime('2025-06-01');

        $target = TestHelper::createAgendamento(
            $this->em,
            $cliente,
            $unidade,
            $servico,
            $data,
            new DateTime('09:00'),
            'unique-oid-abc',
        );
        TestHelper::createAgendamento(
            $this->em,
            $cliente,
            $unidade,
            $servico,
            $data,
            new DateTime('10:00'),
            'other-oid-xyz',
        );

        $client->request('GET', '/api/agendamentos', ['q' => ['oid:unique-oid-abc']], server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseIsSuccessful();

        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $result);
        $this->assertSame($target->getId(), $result[0]['id']);
    }

    public function testSearchByClienteNome(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $unidade = TestHelper::createUnidade($this->em);
        $servico = TestHelper::createServico($this->em);
        $data = new DateTime('2025-06-01');

        $clienteA = TestHelper::createCliente($this->em, 'João Silva', '11111111111');
        $clienteB = TestHelper::createCliente($this->em, 'Maria Souza', '22222222222');

        $target = TestHelper::createAgendamento($this->em, $clienteA, $unidade, $servico, $data, new DateTime('09:00'));
        TestHelper::createAgendamento($this->em, $clienteB, $unidade, $servico, $data, new DateTime('10:00'));

        $client->request('GET', '/api/agendamentos', ['q' => ['cliente.nome:João%']], server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseIsSuccessful();

        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $result);
        $this->assertSame($target->getId(), $result[0]['id']);
    }

    public function testSearchByClienteDocumento(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $unidade = TestHelper::createUnidade($this->em);
        $servico = TestHelper::createServico($this->em);
        $data = new DateTime('2025-06-01');

        $clienteA = TestHelper::createCliente($this->em, 'Cliente A', '33333333333');
        $clienteB = TestHelper::createCliente($this->em, 'Cliente B', '44444444444');

        $target = TestHelper::createAgendamento($this->em, $clienteA, $unidade, $servico, $data, new DateTime('09:00'));
        TestHelper::createAgendamento($this->em, $clienteB, $unidade, $servico, $data, new DateTime('10:00'));

        $client->request('GET', '/api/agendamentos', ['q' => ['cliente.documento:33333333333']], server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseIsSuccessful();

        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $result);
        $this->assertSame($target->getId(), $result[0]['id']);
    }

    public function testSearchByClienteTelefoneWithExactDigits(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $unidade = TestHelper::createUnidade($this->em);
        $servico = TestHelper::createServico($this->em);
        $data = new DateTime('2025-06-01');

        $clienteA = TestHelper::createCliente($this->em, 'Cliente A', '55555555555', '11987654321');
        $clienteB = TestHelper::createCliente($this->em, 'Cliente B', '66666666666', '21912345678');

        $target = TestHelper::createAgendamento($this->em, $clienteA, $unidade, $servico, $data, new DateTime('09:00'));
        TestHelper::createAgendamento($this->em, $clienteB, $unidade, $servico, $data, new DateTime('10:00'));

        $client->request('GET', '/api/agendamentos', ['q' => ['cliente.telefone:11987654321']], server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseIsSuccessful();

        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $result);
        $this->assertSame($target->getId(), $result[0]['id']);
    }

    public function testSearchByClienteTelefoneWithFormattingStripped(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $unidade = TestHelper::createUnidade($this->em);
        $servico = TestHelper::createServico($this->em);
        $data = new DateTime('2025-06-01');

        // stored with formatting
        $clienteA = TestHelper::createCliente($this->em, 'Cliente A', '77777777777', '(11) 98765-4321');
        $clienteB = TestHelper::createCliente($this->em, 'Cliente B', '88888888888', '(21) 91234-5678');

        $target = TestHelper::createAgendamento($this->em, $clienteA, $unidade, $servico, $data, new DateTime('09:00'));
        TestHelper::createAgendamento($this->em, $clienteB, $unidade, $servico, $data, new DateTime('10:00'));

        // search with formatting — DB strips both sides for comparison
        $client->request('GET', '/api/agendamentos', ['q' => ['cliente.telefone:(11) 98765-4321']], server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseIsSuccessful();

        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $result);
        $this->assertSame($target->getId(), $result[0]['id']);
    }

    public function testSearchByServicoId(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $unidade = TestHelper::createUnidade($this->em);
        $servicoA = TestHelper::createServico($this->em, 'Servico A');
        $servicoB = TestHelper::createServico($this->em, 'Servico B');
        $servicoC = TestHelper::createServico($this->em, 'Servico C');
        $cliente = TestHelper::createCliente($this->em, 'Test Cliente');
        $data = new DateTime('2025-06-01');

        $targetA = TestHelper::createAgendamento(
            $this->em,
            $cliente,
            $unidade,
            $servicoA,
            $data,
            new DateTime('09:00'),
        );
        $targetB = TestHelper::createAgendamento(
            $this->em,
            $cliente,
            $unidade,
            $servicoB,
            $data,
            new DateTime('10:00'),
        );
        TestHelper::createAgendamento($this->em, $cliente, $unidade, $servicoC, $data, new DateTime('11:00'));

        $ids = sprintf('%d,%d', $servicoA->getId(), $servicoB->getId());

        $client->request('GET', '/api/agendamentos', ['q' => [sprintf('servico.id:%s', $ids)]], server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseIsSuccessful();

        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(2, $result);

        $resultIds = array_column($result, 'id');
        $this->assertContains($targetA->getId(), $resultIds);
        $this->assertContains($targetB->getId(), $resultIds);
    }

    public function testSearchByUnidadeId(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $unidadeA = TestHelper::createUnidade($this->em, 'Unidade A');
        $unidadeB = TestHelper::createUnidade($this->em, 'Unidade B');
        $servico = TestHelper::createServico($this->em);
        $cliente = TestHelper::createCliente($this->em, 'Test Cliente');
        $data = new DateTime('2025-06-01');

        $target = TestHelper::createAgendamento($this->em, $cliente, $unidadeA, $servico, $data, new DateTime('09:00'));
        TestHelper::createAgendamento($this->em, $cliente, $unidadeB, $servico, $data, new DateTime('10:00'));

        $client->request('GET', '/api/agendamentos', ['q' => [sprintf('unidade.id:%d', $unidadeA->getId())]], server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseIsSuccessful();

        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $result);
        $this->assertSame($target->getId(), $result[0]['id']);
    }

    public function testSearchByMultipleFields(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $unidadeA = TestHelper::createUnidade($this->em, 'Unidade A');
        $unidadeB = TestHelper::createUnidade($this->em, 'Unidade B');
        $servico = TestHelper::createServico($this->em);
        $clienteA = TestHelper::createCliente($this->em, 'Cliente A', '99999999991');
        $clienteB = TestHelper::createCliente($this->em, 'Cliente B', '99999999992');

        $data1 = new DateTime('2025-07-01');
        $data2 = new DateTime('2025-07-02');

        // target: clienteA + data1 + unidadeA
        $target = TestHelper::createAgendamento(
            $this->em,
            $clienteA,
            $unidadeA,
            $servico,
            $data1,
            new DateTime('09:00'),
        );
        // same unidade, different data
        TestHelper::createAgendamento($this->em, $clienteA, $unidadeA, $servico, $data2, new DateTime('09:00'));
        // same data, different unidade
        TestHelper::createAgendamento($this->em, $clienteA, $unidadeB, $servico, $data1, new DateTime('10:00'));
        // different cliente
        TestHelper::createAgendamento($this->em, $clienteB, $unidadeA, $servico, $data1, new DateTime('11:00'));

        // search by unidade only
        $client->request('GET', '/api/agendamentos', [
            'q' => [
                sprintf('unidade.id:%d', $unidadeA->getId()),
            ],
        ], server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseIsSuccessful();

        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(3, $result);

        // search by unidade, and data
        $client->request('GET', '/api/agendamentos', [
            'q' => [
                sprintf('unidade.id:%d', $unidadeA->getId()),
                'data:2025-07-01',
            ],
        ], server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseIsSuccessful();

        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(2, $result);

        // search by unidade, data, and documento
        $client->request('GET', '/api/agendamentos', [
            'q' => [
                sprintf('unidade.id:%d', $unidadeA->getId()),
                'data:2025-07-01',
                sprintf('cliente.documento:%s', $clienteA->getDocumento()),
            ],
        ], server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseIsSuccessful();

        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $result);
        $this->assertSame($target->getId(), $result[0]['id']);
    }

    public function testSearchByClienteDocumentoFormattedStoredSearchDigitsOnly(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $unidade = TestHelper::createUnidade($this->em);
        $servico = TestHelper::createServico($this->em);
        $data = new DateTime('2025-06-01');

        $clienteA = TestHelper::createCliente($this->em, 'Cliente A', '333.333.333-33');
        $clienteB = TestHelper::createCliente($this->em, 'Cliente B', '444.444.444-44');

        $target = TestHelper::createAgendamento($this->em, $clienteA, $unidade, $servico, $data, new DateTime('09:00'));
        TestHelper::createAgendamento($this->em, $clienteB, $unidade, $servico, $data, new DateTime('10:00'));

        $client->request('GET', '/api/agendamentos', ['q' => ['cliente.documento:33333333333']], server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseIsSuccessful();

        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $result);
        $this->assertSame($target->getId(), $result[0]['id']);
    }

    public function testSearchByClienteDocumentoDigitsStoredSearchFormatted(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $unidade = TestHelper::createUnidade($this->em);
        $servico = TestHelper::createServico($this->em);
        $data = new DateTime('2025-06-01');

        $clienteA = TestHelper::createCliente($this->em, 'Cliente A', '55555555555');
        $clienteB = TestHelper::createCliente($this->em, 'Cliente B', '66666666666');

        $target = TestHelper::createAgendamento($this->em, $clienteA, $unidade, $servico, $data, new DateTime('09:00'));
        TestHelper::createAgendamento($this->em, $clienteB, $unidade, $servico, $data, new DateTime('10:00'));

        $client->request('GET', '/api/agendamentos', ['q' => ['cliente.documento:555.555.555-55']], server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseIsSuccessful();

        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $result);
        $this->assertSame($target->getId(), $result[0]['id']);
    }

    public function testSearchByClienteTelefoneFormattedStoredSearchDigitsOnly(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $unidade = TestHelper::createUnidade($this->em);
        $servico = TestHelper::createServico($this->em);
        $data = new DateTime('2025-06-01');

        $clienteA = TestHelper::createCliente($this->em, 'Cliente A', 'doc-tel-a', '(11) 98765-4321');
        $clienteB = TestHelper::createCliente($this->em, 'Cliente B', 'doc-tel-b', '(21) 91234-5678');

        $target = TestHelper::createAgendamento($this->em, $clienteA, $unidade, $servico, $data, new DateTime('09:00'));
        TestHelper::createAgendamento($this->em, $clienteB, $unidade, $servico, $data, new DateTime('10:00'));

        $client->request('GET', '/api/agendamentos', ['q' => ['cliente.telefone:11987654321']], server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseIsSuccessful();

        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $result);
        $this->assertSame($target->getId(), $result[0]['id']);
    }

    public function testSearchByClienteDataNascimento(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $unidade = TestHelper::createUnidade($this->em);
        $servico = TestHelper::createServico($this->em);
        $data = new DateTime('2025-06-01');

        $clienteA = TestHelper::createCliente(
            $this->em,
            'Cliente A',
            'doc-nasc-a',
            '1111111111',
            new DateTime('1986-03-29'),
        );
        $clienteB = TestHelper::createCliente(
            $this->em,
            'Cliente B',
            'doc-nasc-b',
            '2222222222',
            new DateTime('1990-07-15'),
        );

        $target = TestHelper::createAgendamento($this->em, $clienteA, $unidade, $servico, $data, new DateTime('09:00'));
        TestHelper::createAgendamento($this->em, $clienteB, $unidade, $servico, $data, new DateTime('10:00'));

        $client->request('GET', '/api/agendamentos', ['q' => ['cliente.dataNascimento:1986-03-29']], server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseIsSuccessful();

        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $result);
        $this->assertSame($target->getId(), $result[0]['id']);
    }

    public function testSearchByClienteDataNascimentoInvalidFormat(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $client->request('GET', '/api/agendamentos', ['q' => ['cliente.dataNascimento:29/03/1986']], server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseStatusCodeSame(500);
    }

    public function testSearchByDataInvalidFormat(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $client->request('GET', '/api/agendamentos', ['q' => ['data:01/06/2025']], server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseStatusCodeSame(500);
    }

    public function testSearchByHoraInvalidFormat(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $client->request('GET', '/api/agendamentos', ['q' => ['hora:9h00']], server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseStatusCodeSame(500);
    }

    public function testSearchIgnoresNonSearchableFields(): void
    {
        $client = static::getClient();
        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $unidade = TestHelper::createUnidade($this->em);
        $servico = TestHelper::createServico($this->em);
        $cliente = TestHelper::createCliente($this->em, 'Test Cliente');
        $data = new DateTime('2025-06-01');

        TestHelper::createAgendamento($this->em, $cliente, $unidade, $servico, $data, new DateTime('09:00'));
        TestHelper::createAgendamento($this->em, $cliente, $unidade, $servico, $data, new DateTime('10:00'));

        // 'situacao' is not in getSearchableFields() — should be ignored, returning all
        $client->request('GET', '/api/agendamentos', ['q' => ['situacao:agendado']], server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseIsSuccessful();

        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(2, $result);
    }
}
