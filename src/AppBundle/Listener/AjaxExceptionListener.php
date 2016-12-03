<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Listener;

use Novosga\Http\Envelope;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * AjaxListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
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