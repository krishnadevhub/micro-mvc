<?php

declare(strict_types=1);

namespace App\Service;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Application logger service wrapping Monolog.
 *
 * @package App\Service
 */
class AppLogger
{
    private const DEFAULT_LOGGER_NAME = 'app';
    private const LOG_FILE = '/app.log';

    /**
     * The Monolog logger instance.
     *
     * @var Logger
     */
    private readonly Logger $logger;

    /**
     * @param string $loggerName The channel name for the logger
     * @param int $errorLevel The minimum log level to handle
     */
    public function __construct(
        string $loggerName = self::DEFAULT_LOGGER_NAME,
        int $errorLevel = Logger::WARNING
    ) {
        $logFile = LOG_PATH . self::LOG_FILE;
        $this->logger = new Logger($loggerName);
        $this->logger->pushHandler(new StreamHandler($logFile, $errorLevel));
    }

    /**
     * Returns the configured Monolog logger instance.
     *
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }
}
