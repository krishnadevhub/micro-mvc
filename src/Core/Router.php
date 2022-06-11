<?php

declare(strict_types=1);

namespace App\Core;

use App\Service\AppLogger;
use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RequestContext;

class Router
{

    /*public function __construct(
        private Container $container
    )
    {  }*/

    public function __construct(
        private ContainerBuilder $containerBuilder
    )
    {  }

    public function resolve(): void
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

            if (class_exists($controller[0])) {
                $classInstance = $this->containerBuilder->get($controller[0]);

                if (method_exists($classInstance, $controller[1])) {
                    $params = array_merge((array) array_slice($matcher, 2));

                    call_user_func_array(
                        [
                            $classInstance,
                            $controller[1],
                        ],
                        $params
                    );
                }
            }
        } catch (MethodNotAllowedException|ResourceNotFoundException|Exception $e) {
            (new AppLogger())->getLogger()->error($e);
            throw $e;
        }
    }
}