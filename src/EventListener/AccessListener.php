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

use App\Security\UserProvider;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * AccessListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[AsEventListener(event: KernelEvents::REQUEST, priority: 7)]
class AccessListener extends AppListener
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authChecker,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        $isApi = $this->isApiRequest($request);
        $isAdmin = $this->isAdminRequest($request);
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
            $role = UserProvider::roleName($isModule);
            $isUserModule = $this->authChecker->isGranted($role);
            $isUserAdmin = $this->authChecker->isGranted('ROLE_ADMIN');
            if (!$isUserAdmin && !$isUserModule) {
                $response = new RedirectResponse("/");
                $event->setResponse($response);
            }
        }
    }
}
