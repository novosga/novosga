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

use App\Entity\PainelSenha;
use App\Tests\TestHelper;
use Doctrine\ORM\EntityManagerInterface;
use Novosga\Entity\ServicoInterface;
use Novosga\Entity\UnidadeInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PainelControllerTest extends WebTestCase
{
    private const TEST_LOCAL = 'guiche';
    private const TEST_NUMERO_LOCAL = 123;
    private const TEST_PRIORIDADE = 'normal';
    private const TEST_MENSAGEM = 'message test';
    private const TEST_PESO = 0;

    private ?EntityManagerInterface $em = null;

    protected function setUp(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);

        TestHelper::removeTestData($this->em);
    }

    public function testGetPainelWithoutAccessToken(): void
    {
        $client = static::getClient();
        $container = static::getContainer();
        $unidade = TestHelper::createUnidade($this->em);

        $url = sprintf('/api/unidades/%s/painel?servicos=1,2,3', $unidade->getId());
        $client->request('GET', $url);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testGetPainel(): void
    {
        $client = static::getClient();

        $unidade1 = TestHelper::createUnidade($this->em);
        $unidade2 = TestHelper::createUnidade($this->em);
        $unidade3 = TestHelper::createUnidade($this->em);
        $servicos = [
            TestHelper::createServico($this->em, 'Service1'),
            TestHelper::createServico($this->em, 'Service2'),
            TestHelper::createServico($this->em, 'Service3'),
        ];

        $this->generateSenhas($unidade1, $servicos);
        $this->generateSenhas($unidade3, $servicos);

        $expectedSenhas = $this->generateSenhas($unidade2, $servicos);

        $accessToken = TestHelper::generateJwtToken(static::getContainer());

        $ids = array_map(fn (ServicoInterface $servico) => $servico->getId(), $servicos);
        $url = sprintf('/api/unidades/%s/painel?servicos=%s', $unidade2->getId(), join(',', $ids));
        $client->request('GET', $url, server: [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $accessToken),
        ]);

        $this->assertResponseStatusCodeSame(200);

        $response = $client->getResponse();
        $result = json_decode($response->getContent(), true);
        $this->assertSameSize($expectedSenhas, $result);

        for ($i = 0; $i < count($expectedSenhas); $i++) {
            // compare last item from db against first item fro API
            $fromDb = $expectedSenhas[count($expectedSenhas) - $i - 1];
            $fromApi = $result[$i];

            $this->assertSame($fromDb->getId(), $fromApi['id']);
            $this->assertSame($fromDb->getSenhaFormatada(), $fromApi['senha']);
            $this->assertSame($fromDb->getSiglaSenha(), $fromApi['siglaSenha']);
            $this->assertSame($fromDb->getNumeroSenha(), $fromApi['numeroSenha']);
            $this->assertSame($fromDb->getLocal(), $fromApi['local']);
            $this->assertSame($fromDb->getNumeroLocal(), $fromApi['numeroLocal']);
            $this->assertSame($fromDb->getPeso(), $fromApi['peso']);
            $this->assertSame($fromDb->getPrioridade(), $fromApi['prioridade']);
            $this->assertSame($fromDb->getNomeCliente(), $fromApi['nomeCliente']);
            $this->assertSame($fromDb->getDocumentoCliente(), $fromApi['documentoCliente']);
            $this->assertSame($fromDb->getServico()->getId(), $fromApi['servico']['id']);
            $this->assertSame($fromDb->getServico()->getNome(), $fromApi['servico']['nome']);
        }
    }

    /**
     * @param ServicoInterface[] $servicos
     * @return PainelSenha[]
     */
    private function generateSenhas(UnidadeInterface $unidade, array $servicos): array
    {
        return array_map(
            fn (ServicoInterface $servico) => $this->generateSenha($unidade, $servico),
            $servicos,
        );
    }

    private function generateSenha(UnidadeInterface $unidade, ServicoInterface $servico): PainelSenha
    {
        $senha = (new PainelSenha())
            ->setUnidade($unidade)
            ->setServico($servico)
            ->setSiglaSenha('ABC')
            ->setNumeroSenha(100 + rand(0, 100))
            ->setLocal(self::TEST_LOCAL)
            ->setNumeroLocal(self::TEST_NUMERO_LOCAL)
            ->setPrioridade(self::TEST_PRIORIDADE)
            ->setMensagem(self::TEST_MENSAGEM)
            ->setPeso(self::TEST_PESO);

        $this->em->persist($senha);
        $this->em->flush();

        return $senha;
    }
}
