<?php

declare(strict_types=1);

namespace App\Core;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RequestContext;

class Router
{
    public function __invoke(): void
    {
        $context = new RequestContext();
        $context->fromRequest(Request::createFromGlobals());

        try {
            $fileLocator = new FileLocator([CONFIG_PATH]);
            $router = new \Symfony\Component\Routing\Router(
                new YamlFileLoader($fileLocator),
                'routes.yaml',
                ['cache_dir' => CACHE_PATH],
                $context
            );

            $matcher = $router->match($context->getPathInfo());

            $controller = explode('::', $matcher['_controller']);
            $classInstance = new $controller[0]();

            $params = array_merge((array) array_slice($matcher, 2));

            call_user_func_array(
                [
                    $classInstance,
                    $controller[1],
                ],
                $params
            );

        } catch (MethodNotAllowedException|ResourceNotFoundException|Exception $e) {
           // TODO: log error;
            throw $e;
        }
    }
}