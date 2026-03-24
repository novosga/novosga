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

use DateTime;
use DateTimeZone;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\Exception\InvalidFormat;

/**
 * https://www.doctrine-project.org/projects/doctrine-orm/en/3.6/cookbook/working-with-datetime.html
 */
class UTCDateTimeType extends DateTimeType
{
    private static DateTimeZone $utc;

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof DateTime) {
            $value->setTimezone(self::getUtc());
        }

        return parent::convertToDatabaseValue($value, $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?DateTime
    {
        if (null === $value || $value instanceof DateTime) {
            return $value;
        }
        $converted = DateTime::createFromFormat(
            $platform->getDateTimeFormatString(),
            (string) $value,
            self::getUtc()
        );
        if (! $converted) {
            throw InvalidFormat::new(
                (string) $value,
                DateTime::class,
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
