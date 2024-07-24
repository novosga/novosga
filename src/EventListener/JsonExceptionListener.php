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

use Novosga\Http\Envelope;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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
        if (KernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            return;
        }
        
        $exception = $event->getThrowable();
        $request   = $event->getRequest();
        $debug     = $this->kernel->getEnvironment() === 'dev';
        
        $error = $exception->getMessage();
        $detail = null;
        $statusCode = match (true) {
            $exception instanceof HttpException => $exception->getStatusCode(),
            $exception instanceof AuthenticationException => 401,
            $exception instanceof AccessDeniedException => 403,
            default => 500,
        };
        
        if ($debug) {
            $json['detail'] = $exception->getTraceAsString();
        }

        if ($this->isApiRequest($request)) {
            $event->setResponse(new JsonResponse([
                'code' => $statusCode,
                'error' => $error,
                'detail' => $detail,
            ], $statusCode));
        } else if ($request->isXmlHttpRequest()) {
            $envelope = new Envelope();
            $envelope->exception($exception, $debug);
            
            if ($exception instanceof AuthenticationException) {
                $error = $this->translator->trans('session.invalid');
                $envelope->setSessionStatus('inactive');
                $envelope->setMessage($error);
            }
            
            $response = new JsonResponse($envelope, $statusCode);
            $event->setResponse($response);
        }
    }
}
