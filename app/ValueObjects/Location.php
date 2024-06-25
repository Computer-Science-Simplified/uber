<?php

namespace App\ValueObjects;

class Location
{
    public function __construct(
        public readonly float $longitude,
        public readonly float $latitude,
    )
    {
    }

    public static function create(float $longitude, $latitude): self
    {
        return new static($longitude, $latitude);
    }
}
