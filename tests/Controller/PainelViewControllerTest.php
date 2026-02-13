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

namespace App\Tests\Controller;

use App\Tests\TestHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * PainelViewControllerTest
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class PainelViewControllerTest extends WebTestCase
{
    private ?EntityManagerInterface $em = null;

    protected function setUp(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);

        TestHelper::removeTestData($this->em);
    }

    public function testDisplayPainelPage(): void
    {
        $client = static::createClient();
        $unidade = TestHelper::createUnidade($this->em);

        $url = sprintf('/painel/display/%s?servicos=1,2,3', $unidade->getId());
        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorExists('#painel-display');
    }

    public function testDisplayPainelPageWithDefaultTransition(): void
    {
        $client = static::createClient();
        $unidade = TestHelper::createUnidade($this->em);

        $url = sprintf('/painel/display/%s?servicos=1&transition=default', $unidade->getId());
        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $content = $client->getResponse()->getContent();
        $this->assertStringContainsString('default', $content);
    }

    public function testDisplayPainelPageWithSlideLeftTransition(): void
    {
        $client = static::createClient();
        $unidade = TestHelper::createUnidade($this->em);

        $url = sprintf('/painel/display/%s?servicos=1&transition=slide-left', $unidade->getId());
        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $content = $client->getResponse()->getContent();
        $this->assertStringContainsString('slide-left', $content);
    }

    public function testDisplayPainelPageWithCustomInterval(): void
    {
        $client = static::createClient();
        $unidade = TestHelper::createUnidade($this->em);

        $customInterval = 10000;
        $url = sprintf('/painel/display/%s?servicos=1&interval=%d', $unidade->getId(), $customInterval);
        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $content = $client->getResponse()->getContent();
        $this->assertStringContainsString((string)$customInterval, $content);
    }

    public function testDisplayPainelPageContainsVueApp(): void
    {
        $client = static::createClient();
        $unidade = TestHelper::createUnidade($this->em);

        $url = sprintf('/painel/display/%s?servicos=1', $unidade->getId());
        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $content = $client->getResponse()->getContent();

        // Check Vue.js integration
        $this->assertStringContainsString('new Vue', $content);
        $this->assertStringContainsString('el: \'#painel-display\'', $content);

        // Check playlist functionality
        $this->assertStringContainsString('senhas:', $content);
        $this->assertStringContainsString('currentIndex:', $content);
        $this->assertStringContainsString('nextSenha', $content);
        $this->assertStringContainsString('fetchSenhas', $content);
    }

    public function testDisplayPainelPageContainsPlaylistContainer(): void
    {
        $client = static::createClient();
        $unidade = TestHelper::createUnidade($this->em);

        $url = sprintf('/painel/display/%s', $unidade->getId());
        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.playlist-container');
        $this->assertSelectorExists('.clock-display');
        $this->assertSelectorExists('.unit-name');
    }

    public function testDisplayPainelPageWithInvalidUnidade(): void
    {
        $client = static::createClient();

        $invalidId = 999999;
        $url = sprintf('/painel/display/%s', $invalidId);
        $client->request('GET', $url);

        $this->assertResponseStatusCodeSame(404);
    }
}
