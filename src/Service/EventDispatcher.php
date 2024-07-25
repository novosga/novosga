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

namespace App\Service;

use App\Event\AdvancedEvent;
use App\Event\Event;
use App\Event\EventInterface;
use App\Event\EventDispatcherInterface;
use App\Event\LoggerAwareEventInterface;
use App\Event\StorageAwareEventInterface;
use App\Event\UserAwareEventInterface;
use App\Service\Configuration;
use Novosga\Entity\UsuarioInterface;
use Novosga\Infrastructure\StorageInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * EventDispatcher
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class EventDispatcher implements EventDispatcherInterface
{
    private ?UsuarioInterface $user = null;

    public function __construct(
        private readonly Configuration $config,
        private readonly LoggerInterface $logger,
        private readonly StorageInterface $storage,
        TokenStorageInterface $token,
    ) {
        $user = $token->getToken()?->getUser();
        if ($user instanceof UsuarioInterface) {
            $this->user = $user;
        }
    }

    /** {@inheritdoc} */
    public function dispatch(EventInterface $event): bool
    {
        $eventName = $event->getName();
        $hookKey = "hooks.{$eventName}";
        $callback = $this->config->get($hookKey);

        if (is_callable($callback)) {
            return !!$callback($event);
        }

        return false;
    }

    /** {@inheritdoc} */
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
