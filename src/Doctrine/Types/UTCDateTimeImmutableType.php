<?php

/*
 * This file is part of the NovoSGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Doctrine\Types;

use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\DBAL\Types\Exception\InvalidFormat;

/**
 * https://www.doctrine-project.org/projects/doctrine-orm/en/3.6/cookbook/working-with-datetime.html
 */
class UTCDateTimeImmutableType extends DateTimeImmutableType
{
    private static DateTimeZone $utc;

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof DateTimeImmutable) {
            $value = $value->setTimezone(self::getUtc());
        }

        return parent::convertToDatabaseValue($value, $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?DateTimeImmutable
    {
        if (null === $value || $value instanceof DateTimeImmutable) {
            return $value;
        }
        $converted = DateTimeImmutable::createFromFormat(
            $platform->getDateTimeFormatString(),
            (string) $value,
            self::getUtc()
        );
        if (! $converted) {
            throw InvalidFormat::new(
                (string) $value,
                DateTimeImmutable::class,
                $platform->getDateTimeFormatString()
            );
        }
        return $converted;
    }

    private static function getUtc(): DateTimeZone
    {
        return self::$utc ??= new DateTimeZone('UTC');
    }
}
