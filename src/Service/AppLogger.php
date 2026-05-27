<?php

declare(strict_types=1);

namespace App\Service;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class AppLogger
{
    private readonly Logger $logger;

    public function __construct(string $loggerName = 'app', int $errorLevel = Logger::WARNING)
    {
        $logFile = LOG_PATH . '/app.log';
        $this->logger = new Logger($loggerName);
        $this->logger->pushHandler(new StreamHandler($logFile, $errorLevel));
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }
}
