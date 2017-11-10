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

use FOS\OAuthServerBundle\Security\Firewall\OAuthListener as ListenerBase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

/**
 * OAuthListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class OAuthListener implements ListenerInterface
{
    /**
     * @var Container
     */
    private $container;
    
    /**
     * @var AuthenticationManagerInterface
     */
    private $manager;
    
    /**
     * @var TokenStorageInterface $storeage
     */
    private $storage;
    
    public function __construct(
        Container $container,
        AuthenticationManagerInterface $manager,
        TokenStorageInterface $storage
    ) {
        $this->container = $container;
        $this->manager   = $manager;
        $this->storage   = $storage;
    }
    
    public function handle(GetResponseEvent $event)
    {
        $server  = $this->container->get('fos_oauth_server.server');
        
        $session = $event->getRequest()->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }
        
        $serializedToken = $session->get('_security_main');
        $token = unserialize($serializedToken);
        
        if ($token instanceof TokenInterface) {
            $this->storage->setToken($token);
        }
        
        $wrapped = new ListenerBase($this->storage, $this->manager, $server);
        
        return $wrapped->handle($event);
    }
}
