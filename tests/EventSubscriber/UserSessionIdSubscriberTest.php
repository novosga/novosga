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

namespace App\Tests\EventSubscriber;

use App\Entity\Usuario;
use App\EventSubscriber\UserSessionIdSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use League\Bundle\OAuth2ServerBundle\Security\Authentication\Token\OAuth2Token;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * UserSessionIdSubscriberTest
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class UserSessionIdSubscriberTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private MockClock $clock;
    private RequestStack $requestStack;
    private UserSessionIdSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->clock = new MockClock('2026-08-20 10:00:00');
        $this->requestStack = new RequestStack();
        $this->subscriber = new UserSessionIdSubscriber($this->requestStack, $this->em, $this->clock);
    }

    public function testSubscribedEvents(): void
    {
        $this->assertSame(
            [LoginSuccessEvent::class => 'onLoginSuccess'],
            UserSessionIdSubscriber::getSubscribedEvents(),
        );
    }

    public function testOnLoginSuccessUpdatesSessionIdUltimoAcessoAndIp(): void
    {
        $request = Request::create('/login');
        $request->server->set('REMOTE_ADDR', '203.0.113.42');
        $session = new Session(new MockArraySessionStorage());
        $session->setId('abc123');
        $request->setSession($session);
        $this->requestStack->push($request);

        $usuario = new Usuario();
        $token = $this->createMock(TokenInterface::class);
        $event = $this->createMock(LoginSuccessEvent::class);
        $event->method('getAuthenticatedToken')->willReturn($token);
        $event->method('getUser')->willReturn($usuario);

        $this->em->expects($this->once())->method('persist')->with($usuario);
        $this->em->expects($this->once())->method('flush');

        $this->subscriber->onLoginSuccess($event);

        $this->assertSame('abc123', $usuario->getSessionId());
        $this->assertSame('203.0.113.42', $usuario->getIp());
        $this->assertInstanceOf(\DateTime::class, $usuario->getUltimoAcesso());
        $this->assertSame(
            $this->clock->now()->getTimestamp(),
            $usuario->getUltimoAcesso()->getTimestamp(),
        );
    }

    public function testOnLoginSuccessSkipsOAuth2Tokens(): void
    {
        $usuario = new Usuario();
        $token = $this->createMock(OAuth2Token::class);
        $event = $this->createMock(LoginSuccessEvent::class);
        $event->method('getAuthenticatedToken')->willReturn($token);
        $event->expects($this->never())->method('getUser');

        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->never())->method('flush');

        $this->subscriber->onLoginSuccess($event);

        $this->assertNull($usuario->getSessionId());
        $this->assertNull($usuario->getIp());
        $this->assertNull($usuario->getUltimoAcesso());
    }
}
