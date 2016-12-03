<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Security;

use Doctrine\ORM\EntityManager;
use Novosga\Entity\Usuario;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * SecurityListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class SecurityListener 
{
    private $em;
    
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }
    
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $usuario = $event->getAuthenticationToken()->getUser();
        
        $sessionId = $event->getRequest()->getSession()->getId();
        $usuario->setSessionId($sessionId);
        $this->em->merge($usuario);
        $this->em->flush();
        
        $repository = $this->em->getRepository(Usuario::class);
        $unidade = $repository->loadUnidade($usuario);
        $repository->updateUnidade($usuario, $unidade);
    }
}
