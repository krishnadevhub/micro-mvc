<?php

namespace App\Core;

use Exception;
use RuntimeException;
use Symfony\Component\Dotenv\Dotenv;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class Application
{
    public static string $environment;

    /**
     * Initiates packages based on project environment.
     *
     * @return void
     * @throws Exception
     */
    public static function init(): void
    {
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');

        self::$environment = $_ENV['APP_ENV'];
        self::isValidEnvironment();

        if (self::isDevelopment()) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL && E_NOTICE);

            $whoops = new Run();
            $whoops->pushHandler(new PrettyPageHandler());
            $whoops->register();
        }

        try {
            (new Router)();
        } catch (Exception $e) {
            // TODO: log error;
            throw $e;
        }
    }

    /**
     * Checks environment variables are valid.
     *
     * @return void
     */
    private static function isValidEnvironment(): void
    {
        if (empty(self::$environment)) {
            throw new RuntimeException('The environment cannot be empty. Please specify in .env file');
        }

        if (!in_array(self::$environment, ['dev','development', 'prod', 'production'])) {
            throw new RuntimeException(sprintf(
                'Invalid environment "%s" provided. Supported variables are: dev, development, prod, production',
                self::$environment)
            );
        }
    }

    /**
     * Checks project running under development environment.
     *
     * @return bool
     */
    public static function isDevelopment(): bool
    {
        if (in_array(self::$environment, ['dev', 'development'])) {
            return true;
        }

        return false;
    }

    /**
     * Checks project running under production environment.
     *
     * @return bool
     */
    public function isProduction(): bool
    {
        if (in_array(self::$environment, ['prod', 'production'])) {
            return true;
        }

        return false;
    }

    /**
     * Get full qualified domain url with host.
     *
     * @return string
     */
    public static function getBaseUrl(): string
    {
        $ssl = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on';
        $sp = strtolower($_SERVER['SERVER_PROTOCOL']);
        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        $port = $_SERVER['SERVER_PORT'];
        $port = ((!$ssl && '80' === $port) || ($ssl && '443' === $port)) ? '' : ':'.$port;
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? ($_SERVER['HTTP_HOST'] ?? null);
        $host = $host ?? $_SERVER['SERVER_NAME'] . $port;

        return $protocol.'://'.$host;
    }
}