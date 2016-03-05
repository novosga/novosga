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
        $unidades = $this->em->getRepository(Unidade::class)->findByUsuario($usuario);
        
        if (count($unidades) === 1) {
            $this->updateUnidade($event->getRequest(), $usuario, $unidades[0]);
        }
    }
    
    public function updateUnidade(Request $request, Usuario $usuario, Unidade $unidade)
    {
        $this->em->getRepository(Usuario::class)->loadRoles($usuario, $unidade);
        $request->getSession()->set('unidade', $unidade);
    }
}
