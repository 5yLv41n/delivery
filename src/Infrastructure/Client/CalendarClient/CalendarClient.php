<?php

namespace App\Infrastructure\Client\CalendarClient;

use App\Domain\Exception\CalendarClientException;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;

class CalendarClient
{
    public function __construct(
    ) {
    }

    /**
    * @param array<int, mixed> $parsedEvents
    */
    public function createEvent(string $calendarId, array $parsedEvents): void
    {
        $service = new Calendar($this->getClient());

        foreach ($parsedEvents as $parsedEvent) {
            $event = new Event();
            $event->setSummary($parsedEvent['summary']);
            $event->setLocation($parsedEvent['location']);
            $eventDateTime = new EventDateTime();
            $eventDateTime->setDate($parsedEvent['start']);
            $event->setStart($eventDateTime);
            $event->setEnd($eventDateTime);

            $service->events->insert($calendarId, $event);
        }
    }

    private function getClient(): Client
    {
        $client = new Client();
        $client->setApplicationName('Delivery Calendar');
        $client->setScopes('https://www.googleapis.com/auth/calendar');
        $client->setAuthConfig('credentials.json');
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        $redirect_uri = 'https://foo.bar';
        $client->setRedirectUri($redirect_uri);

        $tokenPath = 'token.json';
        if (true === file_exists($tokenPath)) {
            $token = file_get_contents($tokenPath);
            if (false === $token) {
                throw new CalendarClientException("An error occurred during parsing $tokenPath");
            }
            $accessToken = json_decode($token, true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $stdin = fgets(STDIN);

                if (false === $stdin) {
                    throw new CalendarClientException('An error occurred during code verification');
                }

                $authCode = trim($stdin);

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (true === array_key_exists('error', $accessToken)) {
                    throw new CalendarClientException(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (false === file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }

        return $client;
    }
}
