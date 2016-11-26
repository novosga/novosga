<?php

namespace AppBundle\Security;

use Doctrine\ORM\EntityManager;
use Novosga\Entity\Unidade;
use Novosga\Entity\Usuario;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * SecurityListener
 *
 * @author rogerio
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
