<?php

declare(strict_types=1);

namespace App\Entity\Types;

use DateTimeImmutable as PHPDateTimeImmutable;
use DateTimeInterface;
use Stringable;

class DateKey extends PHPDateTimeImmutable implements Stringable
{
    public function __toString(): string
    {
        return $this->format(self::ATOM);
    }

    public static function fromDateTimeInterface(DateTimeInterface $source): self
    {
        return new self($source->format(self::ATOM));
    }
}
