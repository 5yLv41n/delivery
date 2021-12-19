<?php

namespace App\UseCase;

use App\Domain\Exception\CreateEventNotFoundException;
use App\Infrastructure\Client\CalendarClient\CalendarClient;
use App\Domain\ValueObject\Event;
use DateTimeInterface;

class CreateEventUseCase
{
    public function __construct(
        private CalendarClient $calendarClient,
    ) {
    }

    /**
    * @param array<int, Event> $parsedEvents
    */
    public function execute(array $parsedEvents): void
    {
        $events = [];
        foreach ($parsedEvents as $parsedEvent) {
            $event = [
                'summary' => 'Livraison '.ucfirst($parsedEvent->getSummary()),
                'location' => $parsedEvent->getLocation(),
                'description' => '',
                'start' => $parsedEvent->getDate()->format('Y-m-d'),
                'end' => $parsedEvent->getDate()->format('Y-m-d'),
            ];
            $events[] = $event;
        }

        if (true === empty($events)) {
            throw new CreateEventNotFoundException('No event found');
        }

        $this->calendarClient->createEvent('primary', $events);
    }
}
