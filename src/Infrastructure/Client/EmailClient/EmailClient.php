<?php

namespace App\Infrastructure\Client\EmailClient;

use App\Domain\Exception\ImapException;
use DateInterval;
use DateTimeImmutable;
use UnexpectedValueException;

final class EmailClient implements EmailClientInterface
{
    public const CRITERIA = [
        'since' => 'SINCE',
        'subject' => 'SUBJECT',
        'body' => 'BODY',
        'from' => 'FROM',
    ];

    private const INBOX_NAME = 'INBOX';

    /** @var resource|null */
    private $connection;

    public function __construct(
        private string $mailboxConnectionString,
        private string $username,
        private string $password,
    ) {
    }

    /**
     * @param array<string, string> $criteria
     *
     * @return array<int, object>
     */
    public function fetchByCriteria(array $criteria = []): array
    {
        $date = new DateTimeImmutable();
        $since = $date->modify('-1 days')->format('d M Y');

        $criterionList = '';
        foreach ($criteria as $key => $value) {
            $criterionList .= $key.' "'.$value.'" ';
        }

        $uids = imap_search($this->getConnection(), $criterionList);
        if (false === $uids) {
            return [];
        }

        $mails = [];
        foreach ($uids as $uid) {
            $result = imap_fetch_overview($this->getConnection(), $uid);
            if (false === $result) {
                continue;
            }
            $mails[] = $result[0];
        }

        return $mails;
    }

    /**
     * @throws ImapException
     */
    public function getBody(int $uid): string
    {
        $body = imap_body($this->getConnection(), $uid);

        if (false === $body) {
            return '';
        }

        return quoted_printable_decode($body);
    }

    /**
     * @throws ImapException
     */
    private function getConnection(): mixed
    {
        if (null !== $this->connection) {
            return $this->connection;
        }

        $this->open();

        return $this->connection;
    }

    /**
     * @throws ImapException
     */
    private function open(): void
    {
        // Add @ in order to avoid internal error with invalid mailbox
        $connection = imap_open(
            $this->mailboxConnectionString.self::INBOX_NAME,
            $this->username,
            $this->password,
        );

        // Prevent exception to be thrown
        imap_errors();

        if (false === $connection) {
            throw new ImapException('Failed to connect to mailbox');
        }

        $this->connection = $connection;
    }
}
