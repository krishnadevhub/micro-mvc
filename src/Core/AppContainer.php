<?php
declare(strict_types=1);

namespace App\Core;

use App\Application;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Cache Services configuration resources.
 * https://symfony.com/doc/current/components/dependency_injection/compilation.html
 */
class AppContainer
{
    private readonly Container $container;

    public function __construct()
    {
        $file = CACHE_PATH.'/container.php';
        $containerConfigCache = new ConfigCache($file, Application::$isDebug);

        if (!$containerConfigCache->isFresh()) {
            $containerBuilder = new ContainerBuilder();
            $loader = new YamlFileLoader($containerBuilder, new FileLocator(CONFIG_PATH));
            $loader->load('services.yaml');

            $containerBuilder->compile();

            $dumper = new PhpDumper($containerBuilder);
            $containerConfigCache->write(
                $dumper->dump(['class' => 'AppCachedContainer']),
                $containerBuilder->getResources()
            );
        }

        require_once $file;
        $this->container = new \AppCachedContainer();
    }

    public function getContainer(): Container
    {
        return $this->container;
    }
}