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

class Application
{
    public static string $environment;
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
        $dotenv->load(__DIR__.'/../.env');

        self::$environment = $_ENV['APP_ENV'];
        self::isValidEnvironment();

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

    /**
     * Defined path constants which are commonly used throughout the framework.
     *
     * @return void
     */
    public static function loadPathConstants(): void
    {
        defined('APP_ROOT') || define('APP_ROOT', dirname($_SERVER['DOCUMENT_ROOT'], 1));
        defined('PUBLIC_PATH') || define('PUBLIC_PATH', 'public');
        defined('CONFIG_PATH') || define('CONFIG_PATH', APP_ROOT . DIRECTORY_SEPARATOR .'config');
        defined('ASSET_PATH') || define('ASSET_PATH', APP_ROOT . DIRECTORY_SEPARATOR . PUBLIC_PATH . DIRECTORY_SEPARATOR .'assets');
        defined('TEMPLATE_PATH') or define('TEMPLATE_PATH', APP_ROOT. DIRECTORY_SEPARATOR .'templates');
        defined('CACHE_PATH') or define('CACHE_PATH',APP_ROOT . DIRECTORY_SEPARATOR .'var'. DIRECTORY_SEPARATOR. 'cache');
        defined('LOG_PATH') or define('LOG_PATH', APP_ROOT . DIRECTORY_SEPARATOR .'var'. DIRECTORY_SEPARATOR .'log');
    }
}