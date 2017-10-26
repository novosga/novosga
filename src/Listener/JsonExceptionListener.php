<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Listener;

use Novosga\Http\Envelope;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * JsonExceptionListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class JsonExceptionListener extends AppListener
{
    private $kernel;
    
    public function __construct(\Symfony\Component\HttpKernel\Kernel $kernel)
    {
        $this->kernel = $kernel;
    }
    
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $request = $event->getRequest();
        
        if ($this->isApiRequest($request)) {
            if ($exception instanceof NotFoundHttpException) {
                $json = [
                    'code' => 404,
                    'error' => 'Not found',
                ];
            } else {
                $json = [
                    'code' => 400,
                    'error' => $exception->getMessage(),
                    'detail' => $exception->getTraceAsString(),
                ];
            }
            
            $response = new JsonResponse($json);
            $event->setResponse($response);
        } else if ($request->isXmlHttpRequest()) {
            $debug = $this->kernel->getEnvironment() === 'dev';
            
            $envelope = new Envelope();
            $envelope->exception($exception, $debug);
            
            if ($exception instanceof AuthenticationException) {
                $envelope->setSessionStatus('inactive');
                $envelope->setMessage(_('Sessão inválida'));
            }
            
            $response = new JsonResponse($envelope);
            $event->setResponse($response);
        }
    }
}
