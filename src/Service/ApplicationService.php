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

use App\Dto\ApplicationSettings;
use App\Dto\Settings\AppearanceSettings;
use App\Dto\Settings\BehaviorSettings;
use App\Dto\Settings\QueueSettings;
use App\Entity\Metadata;
use Novosga\Repository\MetadataRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * ApplicationService
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class ApplicationService
{
    private const APP_NAMESPACE = 'novosga.settings';
    private const APP_APPEARANCE = 'appearance';
    private const APP_BEHAVIOR = 'behavior';
    private const APP_QUEUE = 'queue';

    private ?ApplicationSettings $settings = null;

    public function __construct(
        private readonly NormalizerInterface $normalizer,
        private readonly DenormalizerInterface $denormalizer,
        private readonly MetadataRepositoryInterface $metadataRepository,
    ) {
    }

    public function loadSettings(): ApplicationSettings
    {
        return $this->settings ??= $this->doLoadSettings();
    }

    public function loadAppearanceSettings(): AppearanceSettings
    {
        return $this->getMetadataValue(self::APP_APPEARANCE, AppearanceSettings::class);
    }

    public function loadQueueSettings(): QueueSettings
    {
        return $this->getMetadataValue(self::APP_QUEUE, QueueSettings::class);
    }

    public function loadBehaviorSettings(): BehaviorSettings
    {
        return $this->getMetadataValue(self::APP_BEHAVIOR, BehaviorSettings::class);
    }

    public function saveSettings(ApplicationSettings $settings): void
    {
        $this->setMetadataValue(self::APP_APPEARANCE, $settings->appearance);
        $this->setMetadataValue(self::APP_BEHAVIOR, $settings->behavior);
        $this->setMetadataValue(self::APP_QUEUE, $settings->queue);
    }

    private function doLoadSettings(): ApplicationSettings
    {
        return new ApplicationSettings(
            appearance: $this->loadAppearanceSettings(),
            behavior: $this->loadBehaviorSettings(),
            queue: $this->loadQueueSettings(),
        );
    }

    /**
     * @template T
     * @class-name T
     * @param class-string<T> $type
     * @return T
     */
    private function getMetadataValue(string $name, string $type): mixed
    {
        /** @var Metadata|null */
        $metadata = $this->metadataRepository->findOneBy([
            'namespace' => self::APP_NAMESPACE,
            'name' => $name,
        ]);

        return $this->denormalizer->denormalize($metadata?->getValue(), $type);
    }

    private function setMetadataValue(string $name, mixed $settings): void
    {
        $value = $this->normalizer->normalize($settings);
        $this->metadataRepository->set(
            self::APP_NAMESPACE,
            $name,
            $value,
        );
    }
}
