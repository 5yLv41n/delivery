<?php

namespace App\Domain\Parser;

use App\Domain\ValueObject\Event;
use App\Domain\Exception\ParserException;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use DateTimeInterface;

class ZalandoParser implements ParserInterface
{
    private const NAME = 'zalando';

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
        $pattern = '~Livraison estimée:[\r\n]+(.*)~m';

        preg_match_all($pattern, $body, $matches);

        if (true === empty($matches)) {
            return [];
        }

        $days = explode(' - ', trim($matches[1][1], "\r"));

        $dates = [];
        foreach ($days as $day) {
            $dates[] = $this->convertStringToDateTime(substr($day, strpos($day, ',')+2));
        }

        return $this->getDeliveryDates($dates);
    }

    private function parseLocation(string $body): string
    {
        $pattern = '~Adresse de livraison[\r\n]+(.*)[\r\n]+(.*)[\r\n]+(.*)~m';

        preg_match_all($pattern, $body, $matches);

        return implode(', ', array_map('trim', array_merge(array_unique($matches[1]), array_unique($matches[2]))));
    }

    private function convertStringToDateTime(string $dateAsString): DateTimeInterface
    {
        $date = new DateTimeImmutable();

        $months = [
            'jan.' => '1',
            'fév.' => '2',
            'mar.' => '3',
            'avr.' => '4',
            'mai' => '5',
            'juin' => '6',
            'juillet' => '7',
            'aoû.' => '8',
            'sep.' => '9',
            'oct.' => '10',
            'no.' => '11',
            'déc.' => '12',
        ];

        $pattern = '~(.*) (.*) (.*)$~';
        $dateTimes = [];

        preg_match_all($pattern, trim($dateAsString), $matches);

        $day = $matches[1][0];
        $month = $months[$matches[2][0]];
        $year = $matches[3][0];

        $convertedDate = DateTimeImmutable::createFromFormat('Y/m/d', "$year/$month/$day");

        if (false === $convertedDate) {
            throw new ParserException('An error occurred during converting date');
        }

        return $convertedDate;
    }

    /**
    * @param array<int, DateTimeInterface> $dates
    *
    * @return array<int, DateTimeInterface>
    */
    private function getDeliveryDates(array $dates): array
    {
        $interval = DateInterval::createFromDateString('1 day');
        $dateRanges = new DatePeriod($dates[0], $interval, DateTimeImmutable::createFromInterface($dates[1])->modify('+1 day'));

        $deliveryDates = [];
        foreach ($dateRanges as $dateRange) {
            $deliveryDates[] = $dateRange;
        }

        return $deliveryDates;
    }
}
