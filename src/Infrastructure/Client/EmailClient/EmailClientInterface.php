<?php

namespace App\Infrastructure\Client\EmailClient;

use RuntimeException;

interface EmailClientInterface
{
    /**
     * @param array<string, string> $criteria
     * @throws RuntimeException
     *
     * @return array<int, object>
     */
    public function fetchByCriteria(array $criteria = []): array;

    /**
     * @throws RuntimeException
     */
    public function getBody(int $uid): string;
}
