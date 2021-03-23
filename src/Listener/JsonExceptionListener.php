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
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * JsonExceptionListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class JsonExceptionListener extends AppListener
{
    /**
     * @var KernelInterface
     */
    private $kernel;
    
    /**
     * @var TranslatorInterface
     */
    private $translator;
    
    public function __construct(KernelInterface $kernel, TranslatorInterface $translator)
    {
        $this->kernel     = $kernel;
        $this->translator = $translator;
    }
    
    public function onKernelException(ExceptionEvent $event)
    {
        if (KernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }
        
        $exception = $event->getThrowable();
        $request   = $event->getRequest();
        $debug     = $this->kernel->getEnvironment() === 'dev';
        
        if ($this->isApiRequest($request)) {
            if ($exception instanceof NotFoundHttpException) {
                $json = [
                    'code' => 404,
                    'error' => 'Not found',
                ];
            } else if ($exception instanceof AuthenticationCredentialsNotFoundException) {
                $json = [
                    'code' => 403,
                    'error' => 'Not authenticated',
                ];
            } else {
                $json = [
                    'code' => 400,
                    'error' => $exception->getMessage(),
                ];
            }
            
            if ($debug) {
                $json['detail'] = $exception->getTraceAsString();
            }
            
            $response = new JsonResponse($json);
            $event->setResponse($response);
        } else if ($request->isXmlHttpRequest()) {
            $envelope = new Envelope();
            $envelope->exception($exception, $debug);
            
            if ($exception instanceof AuthenticationException) {
                $error = $this->translator->trans('session.invalid');
                $envelope->setSessionStatus('inactive');
                $envelope->setMessage($error);
            }
            
            $response = new JsonResponse($envelope);
            $event->setResponse($response);
        }
    }
}
