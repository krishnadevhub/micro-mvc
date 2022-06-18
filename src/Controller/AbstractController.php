<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application;
use App\Core\Twig\AssetExtension;
use App\Core\Twig\RoutingExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

abstract class AbstractController
{
    /**
     * Render a view template using Twig
     *
     * @param string $template The template file
     * @param array $args Associative array of data to display in the view (optional)
     *
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function render(string $template, array $args = []): void
    {
        static $twig = null;

        if ($twig === null) {
            $loader = new FilesystemLoader(__DIR__.'/../../templates');
            $twig = new Environment($loader, [
                'cache' => CACHE_PATH,
                'debug' => Application::isDevelopment(),
            ]);

            $context = new RequestContext();
            $context->fromRequest(Request::createFromGlobals());

            $fileLocator = new FileLocator([CONFIG_PATH]);
            $router = new Router(
                new YamlFileLoader($fileLocator),
                'routes.yaml',
                ['cache_dir' => CACHE_PATH],
                $context
            );

            $generator = new UrlGenerator($router->getRouteCollection(), $context);

            $twig->addExtension(new AssetExtension());
            $twig->addExtension(new RoutingExtension($generator));
        }

        echo $twig->render($template, $args);
    }
}