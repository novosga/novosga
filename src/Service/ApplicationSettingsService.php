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

use App\Entity\Metadata;
use Novosga\Repository\MetadataRepositoryInterface;
use Novosga\Service\ApplicationSettingsServiceInterface;
use Novosga\Settings\AppearanceSettings;
use Novosga\Settings\ApplicationSettings;
use Novosga\Settings\BehaviorSettings;
use Novosga\Settings\QueueSettings;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * ApplicationSettingsService
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class ApplicationSettingsService implements ApplicationSettingsServiceInterface
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
        $this->saveAppearanceSettings($settings->appearance);
        $this->saveBehaviorSettings($settings->behavior);
        $this->saveQueueSettings($settings->queue);
    }

    public function saveAppearanceSettings(AppearanceSettings $settings): void
    {
        $this->setMetadataValue(self::APP_APPEARANCE, $settings);
    }

    public function saveBehaviorSettings(BehaviorSettings $settings): void
    {
        $this->setMetadataValue(self::APP_BEHAVIOR, $settings);
    }

    public function saveQueueSettings(QueueSettings $settings): void
    {
        $this->setMetadataValue(self::APP_QUEUE, $settings);
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
