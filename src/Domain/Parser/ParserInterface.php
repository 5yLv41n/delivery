<?php

namespace App\Domain\Parser;

use App\Domain\ValueObject\Event;

interface ParserInterface
{
    public static function getName(): string;

    /**
    * @return array<int, Event>
    */
    public function createEvent(string $body): array;
}
