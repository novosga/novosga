<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use Novosga\Event\AdvancedEvent;
use Novosga\Event\Event;
use Novosga\Event\EventInterface;
use Novosga\Event\EventDispatcherInterface;
use Novosga\Event\LoggerAwareEventInterface;
use Novosga\Event\StorageAwareEventInterface;
use Novosga\Event\UserAwareEventInterface;
use Novosga\Infrastructure\StorageInterface;
use Psr\Log\LoggerInterface;
use Novosga\Service\Configuration;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * EventDispatcher
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var Configuration
     */
    private $config;
    
    /**
     * @var UserInterface
     */
    private $user;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @var StorageInterface
     */
    private $storage;
    
    public function __construct(
        Configuration $config,
        LoggerInterface $logger,
        TokenStorageInterface $token,
        StorageInterface $storage
    ) {
        $this->config  = $config;
        $this->logger  = $logger;
        $this->storage = $storage;
        $this->user    = $token->getToken() ? $token->getToken()->getUser() : null;
    }
    
    /**
     * {@inheritdoc}
     */
    public function dispatch(EventInterface $event): bool
    {
        $eventName = $event->getName();
        $hookKey   = "hooks.{$eventName}";
        $callback  = $this->config->get($hookKey);
        
        if (is_callable($callback)) {
            return !!$callback($event);
        }
        
        return false;
    }
    
    /**
     * {@inheritdoc}
     */
    public function createAndDispatch(string $eventName, $eventData, bool $advancedEvent = false): bool
    {
        if ($advancedEvent) {
            $event = new AdvancedEvent($eventName, $eventData);
        } else {
            $event = new Event($eventName, $eventData);
        }
        
        if ($event instanceof UserAwareEventInterface && $this->user !== null) {
            $event->setUser($this->user);
        }
        
        if ($event instanceof LoggerAwareEventInterface && $this->logger !== null) {
            $event->setLogger($this->logger);
        }
        
        if ($event instanceof StorageAwareEventInterface && $this->storage !== null) {
            $event->setStorage($this->storage);
        }
        
        return $this->dispatch($event);
    }
}
