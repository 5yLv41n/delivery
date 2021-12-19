<?php

namespace App\UseCase;

use App\Domain\Exception\ParserException;
use App\Domain\ParserFactory\ParserFactory;
use App\Domain\ResourceLoader\ParserConfigurationLoader;
use App\Domain\ValueObject\Event;
use App\Infrastructure\Client\EmailClient\EmailClient;
use DateTimeInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class ParserUseCase
{
    public function __construct(
        private ContainerInterface $emailClients,
        private ParserConfigurationLoader $parserConfigurationLoader,
        private LoggerInterface $logger,
    ) {
    }

    /**
    * @param array<int, string> $brands
    *
    * @return array<int, Event>
    */
    public function execute(array $brands, DateTimeInterface $since): array
    {
        $events = [];
        foreach ($brands as $brand) {
            try {
                $parser = ParserFactory::create($brand);
                $config = $this->parserConfigurationLoader->getConfig($brand);
                $from = $config['from'];
                $emailClientIds = $config['email_client'];
                $subject = $config['subject'] ?? null;

                $criteria = [
                    EmailClient::CRITERIA['since'] => $since->format('d M Y'),
                    EmailClient::CRITERIA['from'] => $from,
                    EmailClient::CRITERIA['body'] => $brand,
                ];

                if (null !== $subject) {
                    $criteria[EmailClient::CRITERIA['subject']] = $subject;
                }

                foreach ($emailClientIds as $emailClientId) {
                    $emailClient = $this->emailClients->get($emailClientId);
                    $mails = $emailClient->fetchByCriteria($criteria);
                    foreach ($mails as $mail) {
                        $events = $parser->createEvent($emailClient->getBody($mail->msgno));
                    }
                }
            } catch (InvalidArgumentException $e) {
                $this->logger->warning(
                    __CLASS__. ' error',
                    [
                        'brand' => $brand,
                        'exceptionMessage' => $e->getMessage(),
                    ]
                );

                continue;
            }
        }

        return $events;
    }
}
