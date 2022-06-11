<?php

namespace App\Service;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class AppLogger
{
    private Logger $logger;
    private string $logFile = LOG_PATH.'/app.log';

    public function __construct(string $loggerName = 'app', $errorLevel = Logger::WARNING)
    {
        $this->logger = new Logger($loggerName);
        $this->logger->pushHandler(new StreamHandler($this->logFile, $errorLevel));
    }

    /**
     * @return string
     */
    public function getLogFile(): string
    {
        return $this->logFile;
    }

    /**
     * @param string $logFile
     */
    public function setLogFile(string $logFile): void
    {
        $this->logFile = $logFile;
    }

    /**
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }
}