<?php

namespace App\ValueObjects;

/**
 * Estimated Time of Arrival
 */
class Eta
{
    public function __construct(
        public readonly int $seconds,
        public readonly int $timestamp,
    )
    {
    }

    public static function fromMinutes(int $minutes): self
    {
        return new static(
            seconds: $minutes * 60,
            timestamp: now()->addSeconds($minutes * 60)->timestamp,
        );
    }

    public static function oneMinute(): self
    {
        return self::fromMinutes(1);
    }

    public static function fiveMinutes(): self
    {
        return self::fromMinutes(5);
    }

    public static function fifteenMinutes(): self
    {
        return self::fromMinutes(15);
    }
}
