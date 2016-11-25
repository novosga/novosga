<?php

namespace AppBundle\Listener;

use Novosga\Http\Envelope;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * AjaxListener
 *
 * @author rogerio
 */
class AjaxExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $request = $event->getRequest();
        
        if ($request->isXmlHttpRequest()) {
            $envelope = new Envelope();
            $envelope->exception($exception);
            
            if ($exception instanceof AuthenticationException) {
                $envelope->setSessionStatus('inactive');
                $envelope->setMessage(_('Sessão inválida'));
            }
            
            $response = new JsonResponse($envelope);
            $event->setResponse($response);
        }
    }
}