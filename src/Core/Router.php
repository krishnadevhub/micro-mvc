<?php

declare(strict_types=1);

namespace App\Core;

use App\Service\AppLogger;
use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router as SymfonyRouter;

/**
 * Resolves incoming HTTP requests to controller actions using YAML route definitions.
 *
 * @package App\Core
 */
class Router
{
    /**
     * @param Container $container The compiled dependency injection container
     */
    public function __construct(
        private readonly Container $container
    ) {
    }

    /**
     * Matches the current request against defined routes and dispatches to the appropriate controller.
     *
     * @return void
     * @throws MethodNotAllowedException If the HTTP method is not allowed for the matched route
     * @throws ResourceNotFoundException If no route matches the request
     * @throws RuntimeException If the controller format is invalid
     */
    public function resolve(): void
    {
        $context = new RequestContext();
        $context->fromRequest(Request::createFromGlobals());

        try {
            $fileLocator = new FileLocator([CONFIG_PATH]);
            $router = new SymfonyRouter(
                new YamlFileLoader($fileLocator),
                'routes.yaml',
                ['cache_dir' => CACHE_PATH],
                $context
            );

            $matcher = $router->match($context->getPathInfo());

            $controllerParts = explode('::', $matcher['_controller']);

            if (count($controllerParts) !== 2) {
                throw new RuntimeException(
                    sprintf('Invalid controller format "%s". Expected "Class::method".', $matcher['_controller'])
                );
            }

            [$className, $methodName] = $controllerParts;

            if (!class_exists($className)) {
                throw new ResourceNotFoundException(
                    sprintf('Controller class "%s" does not exist.', $className)
                );
            }

            $classInstance = $this->container->get($className);

            if (!method_exists($classInstance, $methodName)) {
                throw new ResourceNotFoundException(
                    sprintf('Method "%s" not found on controller "%s".', $methodName, $className)
                );
            }

            $params = array_filter(
                $matcher,
                fn(string $key) => !str_starts_with($key, '_'),
                ARRAY_FILTER_USE_KEY
            );

            call_user_func_array([$classInstance, $methodName], $params);

        } catch (MethodNotAllowedException $e) {
            (new AppLogger())->getLogger()->error($e->getMessage());
            (new Response('Method Not Allowed', Response::HTTP_METHOD_NOT_ALLOWED))->send();
        } catch (ResourceNotFoundException $e) {
            (new AppLogger())->getLogger()->error($e->getMessage());
            (new Response('Not Found', Response::HTTP_NOT_FOUND))->send();
        }
    }
}
