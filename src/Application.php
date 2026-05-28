<?php

declare(strict_types=1);

namespace App;

use App\Core\AppContainer;
use App\Core\Router;
use App\Service\AppLogger;
use Exception;
use RuntimeException;
use Symfony\Component\Dotenv\Dotenv;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/**
 * Application bootstrap, environment setup, and path constants.
 *
 * @package App
 */
class Application
{
    private const ENV_DEV = 'dev';
    private const ENV_DEVELOPMENT = 'development';
    private const ENV_PROD = 'prod';
    private const ENV_PRODUCTION = 'production';

    private const VALID_ENVIRONMENTS = [
        self::ENV_DEV,
        self::ENV_DEVELOPMENT,
        self::ENV_PROD,
        self::ENV_PRODUCTION,
    ];

    private const DEV_ENVIRONMENTS = [
        self::ENV_DEV,
        self::ENV_DEVELOPMENT,
    ];

    private const PROD_ENVIRONMENTS = [
        self::ENV_PROD,
        self::ENV_PRODUCTION,
    ];

    /**
     * Current application environment.
     *
     * @var string
     */
    public static string $environment;

    /**
     * Whether the application is running in debug mode.
     *
     * @var bool
     */
    public static bool $isDebug = false;

    /**
     * Initiates packages based on project environment.
     *
     * @return void
     * @throws Exception
     */
    public static function init(): void
    {
        self::loadPathConstants();
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__ . '/../.env');

        self::$environment = $_ENV['APP_ENV'];
        self::validateEnvironment();

        if (self::isDevelopment()) {
            ini_set('display_errors', '1');
            ini_set('display_startup_errors', '1');
            error_reporting(E_ALL);
            self::$isDebug = true;

            $whoops = new Run();
            $whoops->pushHandler(new PrettyPageHandler());
            $whoops->register();
        }

        try {
            $container = new AppContainer();
            (new Router($container->getContainer()))->resolve();
        } catch (Exception $ex) {
            (new AppLogger())->getLogger()->error($ex);
            throw $ex;
        }
    }

    /**
     * Validates that the environment variable is set and contains a supported value.
     *
     * @return void
     * @throws RuntimeException If the environment is empty or unsupported
     */
    private static function validateEnvironment(): void
    {
        if (empty(self::$environment)) {
            throw new RuntimeException(
                'The environment cannot be empty. Please specify in .env file'
            );
        }

        if (!in_array(self::$environment, self::VALID_ENVIRONMENTS, true)) {
            throw new RuntimeException(sprintf(
                'Invalid environment "%s" provided. Supported variables are: %s',
                self::$environment,
                implode(', ', self::VALID_ENVIRONMENTS)
            ));
        }
    }

    /**
     * Checks whether the project is running under a development environment.
     *
     * @return bool
     */
    public static function isDevelopment(): bool
    {
        return in_array(self::$environment, self::DEV_ENVIRONMENTS, true);
    }

    /**
     * Checks whether the project is running under a production environment.
     *
     * @return bool
     */
    public static function isProduction(): bool
    {
        return in_array(self::$environment, self::PROD_ENVIRONMENTS, true);
    }

    /**
     * Gets the fully qualified domain URL with host.
     *
     * @return string
     */
    public static function getBaseUrl(): string
    {
        $ssl = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        $sp = strtolower($_SERVER['SERVER_PROTOCOL']);
        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        $port = $_SERVER['SERVER_PORT'];
        $port = ((!$ssl && '80' === $port) || ($ssl && '443' === $port)) ? '' : ':' . $port;
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? ($_SERVER['HTTP_HOST'] ?? null);
        $host = $host ?? $_SERVER['SERVER_NAME'] . $port;

        return $protocol . '://' . $host;
    }

    /**
     * Defines path constants which are commonly used throughout the framework.
     *
     * @return void
     */
    public static function loadPathConstants(): void
    {
        defined('APP_ROOT') || define('APP_ROOT', dirname($_SERVER['DOCUMENT_ROOT'], 1));
        defined('PUBLIC_PATH') || define('PUBLIC_PATH', 'public');
        defined('CONFIG_PATH') || define('CONFIG_PATH', APP_ROOT . DIRECTORY_SEPARATOR . 'config');
        defined('ASSET_PATH') || define(
            'ASSET_PATH',
            APP_ROOT . DIRECTORY_SEPARATOR . PUBLIC_PATH . DIRECTORY_SEPARATOR . 'build'
        );
        defined('TEMPLATE_PATH') || define('TEMPLATE_PATH', APP_ROOT . DIRECTORY_SEPARATOR . 'templates');
        defined('CACHE_PATH') || define(
            'CACHE_PATH',
            APP_ROOT . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'cache'
        );
        defined('LOG_PATH') || define('LOG_PATH', APP_ROOT . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'log');
    }
}
