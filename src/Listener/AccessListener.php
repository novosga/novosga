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

use App\Repository\ORM\UsuarioRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * AccessListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class AccessListener extends AppListener
{
    private $authChecker;
    
    public function __construct(AuthorizationCheckerInterface $authChecker)
    {
        $this->authChecker = $authChecker;
    }
    
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }
        
        $request  = $event->getRequest();
        $isApi    = $this->isApiRequest($request);
        $isAdmin  = $this->isAdminRequest($request);
        $isModule = $this->isModuleRequest($request);
        
        
        if (!$isApi) {
            if ($isAdmin) {
                if (!$this->authChecker->isGranted([ 'ROLE_ADMIN' ])) {
                    $response = new RedirectResponse("/");
                    $event->setResponse($response);
                    return;
                }
            }

            if ($isModule !== false) {
                $role = UsuarioRepository::roleName($isModule);
                if (!$this->authChecker->isGranted([ 'ROLE_ADMIN', $role ])) {
                    $response = new RedirectResponse("/");
                    $event->setResponse($response);
                    return;
                }
            }
        }
    }
}
