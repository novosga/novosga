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
use Symfony\Component\HttpKernel\Event\RequestEvent;
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
    
    public function onKernelRequest(RequestEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }
        
        $request  = $event->getRequest();
        $isApi    = $this->isApiRequest($request);
        $isAdmin  = $this->isAdminRequest($request);
        $isModule = $this->isModuleRequest($request);
        
        
        if ($isApi) {
            return;
        }

        if ($isAdmin) {
            $isUserAdmin = $this->authChecker->isGranted('ROLE_ADMIN');
            if (!$isUserAdmin) {
                $response = new RedirectResponse("/");
                $event->setResponse($response);
                return;
            }
        }

        if ($isModule !== false) {
            $role = UsuarioRepository::roleName($isModule);
            $isUserModule = $this->authChecker->isGranted($role);
            $isUserAdmin = $this->authChecker->isGranted('ROLE_ADMIN');
            if (!$isUserAdmin && !$isUserModule) {
                $response = new RedirectResponse("/");
                $event->setResponse($response);
            }
        }
    }
}
