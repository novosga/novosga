<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security;

use Doctrine\Common\Persistence\ObjectManager;
use Novosga\Entity\Usuario;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * SecurityListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class SecurityListener
{
    /**
     * @var ObjectManager
     */
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $usuario = $event->getAuthenticationToken()->getUser();

        $sessionId = $event->getRequest()->getSession()->getId();
        $usuario->setSessionId($sessionId);
        $this->om->merge($usuario);
        $this->om->flush();

        $repository = $this->om->getRepository(Usuario::class);
        $unidade = $repository->loadUnidade($usuario);
        if ($unidade) {
            $repository->updateUnidade($usuario, $unidade);
        }
    }
}
