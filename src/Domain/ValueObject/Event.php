<?php

namespace App\Domain\ValueObject;

use DateTimeInterface;

final class Event
{
    public function __construct(
        private string $summary,
        private DateTimeInterface $date,
        private string $location,
    ) {
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    public function getLocation(): string
    {
        return $this->location;
    }
}
