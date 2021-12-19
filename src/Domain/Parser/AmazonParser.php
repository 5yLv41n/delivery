<?php

namespace App\Domain\Parser;

use App\Domain\ValueObject\Event;
use App\Domain\Exception\ParserException;
use DateTimeImmutable;
use DateTimeInterface;

class AmazonParser implements ParserInterface
{
    private const NAME = 'amazon';
    private const LOCATION = 'TRAVER';

    public static function getName(): string
    {
        return self::NAME;
    }

    /**
    * @return array<int, Event>
    */
    public function createEvent(string $body): array
    {
        $brand = self::NAME;
        $dates = $this->parseDate($body);
        $location = $this->parseLocation($body);

        if (false === str_contains($location, self::LOCATION)) {
            return [];
        }

        $events = [];
        foreach ($dates as $date) {
            $events[] = new Event(summary: $brand, date: $date, location: $location);
        }

        return $events;
    }

    /**
    * @return array<int, DateTimeInterface>
    */
    private function parseDate(string $body): array
    {
        $pattern = '~(.*di|manche), (.*)~';

        preg_match_all($pattern, $body, $matches);

        $dates = [];
        foreach ($matches[0] as $dateAsString) {
            $dates[] = $this->convertStringToDateTime($dateAsString);
        }

        return $dates;
    }

    private function parseLocation(string $body): string
    {
        $pattern = '~Votre commande sera expédiée à : [\r\n]+(.*)[\r\n]+(.*)~m';

        preg_match_all($pattern, $body, $matches);

        return implode(', ', array_map('trim', array_merge(array_unique($matches[1]), array_unique($matches[2]))));
    }

    private function convertStringToDateTime(string $dateAsString): DateTimeInterface
    {
        $date = new DateTimeImmutable();

        $months = [
            'janvier' => '1',
            'février' => '2',
            'mars' => '3',
            'avril' => '4',
            'mai' => '5',
            'juin' => '6',
            'juillet' => '7',
            'août' => '8',
            'septembre' => '9',
            'octobre' => '10',
            'novembre' => '11',
            'décembre' => '12',
        ];

        $pattern = '~(.*), (.*) (\d)+$~';
        $year = $date->format('Y');
        $dateTimes = [];

        preg_match_all($pattern, trim($dateAsString), $matches);
        $month = $months[$matches[2][0]];
        $day = $matches[3][0];

        $convertedDate = DateTimeImmutable::createFromFormat('Y/m/d', "$year/$month/$day");

        if (false === $convertedDate) {
            throw new ParserException('An error occurred during converting date');
        }

        return $convertedDate;
    }
}
