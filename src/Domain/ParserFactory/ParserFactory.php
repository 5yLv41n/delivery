<?php

namespace App\Domain\ParserFactory;

use App\Domain\Parser\AmazonParser;
use App\Domain\Parser\ParserInterface;
use App\Domain\Parser\ZalandoParser;
use InvalidArgumentException;

class ParserFactory
{
    private const PARSERS = [
        'amazon',
        'zalando',
    ];

    public static function create(string $parser): ParserInterface
    {
        if (false === in_array($parser, self::PARSERS, true)) {
            throw new InvalidArgumentException("Parser $parser does not exist");
        }

        return match ($parser) {
            'amazon' => new AmazonParser(),
            'zalando' => new ZalandoParser(),
        };
    }
}
