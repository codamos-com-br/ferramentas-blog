<?php

declare(strict_types=1);

namespace App\Entity\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateType;

class DateKeyType extends DateType
{
    /**
     * {@inheritDoc}
     *
     * @param T $value
     *
     * @return (T is null ? null : DateKey)
     *
     * @template T
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        $value = parent::convertToPHPValue($value, $platform);
        if ($value !== NULL) {
            $value = DateKey::fromDateTimeInterface($value);
        }

        return $value;
    }

    public function getName(): string
    {
        return 'DateKey';
    }
}
