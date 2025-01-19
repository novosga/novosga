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

use App\Repository\UnidadeMetadataRepository;
use App\Repository\UsuarioMetadataRepository;
use Novosga\Entity\EntityMetadataInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Entity\UsuarioInterface;
use Novosga\Settings\BehaviorSettings;

/**
 * TicketPrioritySwapService
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class TicketPrioritySwapService
{
    private const NAMESPACE = 'novosga.swap';
    private const NAME = 'priority_count';

    private ?BehaviorSettings $settings;

    public function __construct(
        private readonly ApplicationSettingsService $applicationService,
        private readonly UsuarioMetadataRepository $usuarioMetadataRepository,
        private readonly UnidadeMetadataRepository $unidadeMetadataRepository,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->getSettings()->prioritySwap;
    }

    public function shouldIgnorePriority(UnidadeInterface $unidade, ?UsuarioInterface $usuario): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        return $this->getPriorityCount($unidade, $usuario) >= $this->getSettings()->prioritySwapCount;
    }

    public function setPriorityCount(UnidadeInterface $unidade, ?UsuarioInterface $usuario, int $count): void
    {
        switch ($this->getSettings()->prioritySwapMethod) {
            case 'unity':
                $this->setCountByUnity($unidade, $count);
                break;
            case 'user':
                $this->setCountByUser($usuario, $count);
                break;
        };
    }

    public function getPriorityCount(UnidadeInterface $unidade, ?UsuarioInterface $usuario): int
    {
        return match ($this->getSettings()->prioritySwapMethod) {
            'unity' => $this->getCountByUnity($unidade),
            'user' => $this->getCountByUser($usuario),
            default => 0,
        };
    }

    private function getSettings(): BehaviorSettings
    {
        return $this->settings ??= $this->applicationService->loadBehaviorSettings();
    }

    private function getCountByUnity(UnidadeInterface $unidade): int
    {
        return $this->toInteger($this->unidadeMetadataRepository->get($unidade, self::NAMESPACE, self::NAME));
    }

    private function setCountByUnity(UnidadeInterface $unidade, int $count): void
    {
        $this->unidadeMetadataRepository->set($unidade, self::NAMESPACE, self::NAME, $count);
    }

    private function getCountByUser(?UsuarioInterface $usuario): int
    {
        if (!$usuario) {
            return -1;
        }
        return $this->toInteger($this->usuarioMetadataRepository->get($usuario, self::NAMESPACE, self::NAME));
    }

    private function setCountByUser(UsuarioInterface $usuario, int $count): void
    {
        $this->usuarioMetadataRepository->set($usuario, self::NAMESPACE, self::NAME, $count);
    }

    /**
     * @template T of object
     * @param EntityMetadataInterface<T> $metadata
     */
    private function toInteger(?EntityMetadataInterface $metadata): int
    {
        $value = $metadata ? $metadata->getValue() : 0;
        return $value ?? 0;
    }
}
