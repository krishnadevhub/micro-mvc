<?php

declare(strict_types = 1);

namespace App\Service;

class TestEmailService
{
    public function send(array $to, string $template): bool
    {
        return true;
    }
}
