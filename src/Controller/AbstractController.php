<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Routes;
use App\Core\Twig\AssetExtension;
use App\Core\Twig\RoutingExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
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
                'cache' => __DIR__.'/../../var/dev/twig/cache',
                'debug' => true,
            ]);

            $context = new RequestContext();
            $context->fromRequest(Request::createFromGlobals());
            $generator = new UrlGenerator(Routes::getRoutes(), $context);

            $twig->addExtension(new AssetExtension());
            $twig->addExtension(new RoutingExtension($generator));
        }

        echo $twig->render($template, $args);
    }

}