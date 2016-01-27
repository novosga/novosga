<?php

namespace AppBundle\Security;

use Doctrine\ORM\EntityManager;
use Novosga\Entity\Unidade;
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
        $unidades = $this->em->getRepository(Unidade::class)->findByUsuario($usuario);
        
        if (count($unidades) === 1) {
            $event->getRequest()->getSession()->set('unidade', $unidades[0]);
        }
    }
}
