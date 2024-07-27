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

namespace App\EventListener;

use App\Entity\Usuario;
use Novosga\Http\Envelope;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * SessionListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[AsEventListener(event: KernelEvents::REQUEST, priority: 6)]
class SessionListener extends AppListener
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        $request = $event->getRequest();

        if ($this->isApiRequest($request) || $this->isLoginRequest($request) || !$token) {
            return;
        }

        $user = $token->getUser();
        $sessionId = $request->getSession()->getId();

        if ($user instanceof Usuario && $user->getSessionId() !== $sessionId) {
            $request = $event->getRequest();
            if ($request->isXmlHttpRequest()) {
                $error = $this->translator->trans('session.invalid');
                $envelope = new Envelope();
                $envelope->setSuccess(false);
                $envelope->setSessionStatus('inactive');
                $envelope->setMessage($error);

                $response = new JsonResponse($envelope);
            } else {
                $url = $request->getBaseUrl() . '/logout';
                $response = new RedirectResponse($url);
            }
            $event->setResponse($response);
        }
    }
}
