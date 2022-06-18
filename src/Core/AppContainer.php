<?php

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
 * https://symfony.com/doc/6.0/components/dependency_injection/compilation.html
 */
class AppContainer
{
    private Container $container;

    public function __construct()
    {
        $file = CACHE_PATH.'/container.php';
        $containerConfigCache = new ConfigCache($file, Application::$isDebug);

        if (!$containerConfigCache->isFresh()) {
            $this->container = new ContainerBuilder();
            $loader = new YamlFileLoader($this->container, new FileLocator(CONFIG_PATH));
            $loader->load('services.yaml');

            $this->container->compile();

            $dumper = new PhpDumper($this->container);
            $containerConfigCache->write(
                $dumper->dump(['class' => 'AppCachedContainer']),
                $this->container->getResources()
            );
        }

        require_once $file;
        $this->container = new \AppCachedContainer();
    }

    /**
     * @return ContainerBuilder
     */
    public function getContainer(): Container
    {
        return $this->container;
    }
}