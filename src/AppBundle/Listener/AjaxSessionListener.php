<?php

namespace AppBundle\Listener;

use Novosga\Http\Envelope;
use Novosga\Entity\Usuario;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * SessionListener
 *
 * @author rogerio
 */
class AjaxSessionListener
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;
    
    public function __construct(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }
    
    public function onKernelRequest(GetResponseEvent $event)
    {
        $token = $this->tokenStorage->getToken();

        if ($token) {
            $user = $token->getUser();
            $sessionId = $event->getRequest()->getSession()->getId();

            if ($user instanceof Usuario && $user->getSessionId() !== $sessionId) {

                $request = $event->getRequest();
            
                if ($request->isXmlHttpRequest()) {
                    $envelope = new Envelope();
                    $envelope->setSuccess(false);
                    $envelope->setSessionStatus('inactive');
                    $envelope->setMessage(_('Sessão inválida'));

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