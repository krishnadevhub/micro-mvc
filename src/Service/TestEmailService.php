<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Test service for simulating email dispatch.
 *
 * @package App\Service
 */
class TestEmailService
{
    /**
     * Simulates sending an email to the given recipients.
     *
     * @param array<string> $to List of recipient addresses
     * @param string $template The email template identifier
     * @return bool
     */
    public function send(array $to, string $template): bool
    {
        return true;
    }
}
