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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * SessionListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class SessionListener extends AppListener
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    
    /**
     * @var TranslatorInterface
     */
    private $translator;
    
    public function __construct(TokenStorageInterface $tokenStorage, TranslatorInterface $translator)
    {
        $this->tokenStorage = $tokenStorage;
        $this->translator   = $translator;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            return;
        }

        $token   = $this->tokenStorage->getToken();
        $request = $event->getRequest();
        $session = $request->getSession();

        if (!$this->isApiRequest($request) && $token) {
            $user      = $token->getUser();
            $sessionId = $session ? $session->getId() : '-';

            if ($user instanceof Usuario && $user->getSessionId() !== $sessionId) {
                $request = $event->getRequest();

                // TODO: after upgrading to SF7.1, session ID is being updated during login process
                return;

                if ($request->isXmlHttpRequest()) {
                    $error    = $this->translator->trans('session.invalid');
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
}
