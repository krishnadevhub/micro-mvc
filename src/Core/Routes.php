<?php

namespace App\Core;

use Exception;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class Routes
{
    private static RouteCollection $routes;

    public static function load(): void
    {
        $routes = new RouteCollection();

        $routes->add('homepage',
            new Route('/', ['controller' => 'HomeController', 'method' => 'index',])
        );

        $routes->add('pdo-gen',
            new Route('/pdo-gen', ['controller' => 'PdoEntityGeneratorController', 'method' => 'index',])
        );

        $routes->add('product',
            new Route('/product/{id}', ['controller' => 'ProductController', 'method' => 'show'], ['id' => '[0-9]+'])
        );

        self::$routes = $routes;
    }

    /**
     * @throws Exception
     */
    public static function getRoutes(): RouteCollection
    {
        if (null === self::$routes) {
            throw new Exception('Routes object is empty. Call load() method before calling this function ');
        }
        return self::$routes;
    }
}



