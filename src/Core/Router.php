<?php

namespace App\Core;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class Router
{
    public function __invoke(): void
    {
        $context = new RequestContext();
        $context->fromRequest(Request::createFromGlobals());

        try {
            Routes::load();
            $routes = Routes::getRoutes();

            // routing can match routes with incoming requests
            $matcher = new UrlMatcher($routes, $context);

            $matcher = $matcher->match($_SERVER['REQUEST_URI']);

            array_walk($matcher, function (&$param) {
                if (is_numeric($param)) {
                    $param = (int) $param;
                }
            });

            $className = '\\App\Controller\\'.$matcher['controller'];
            $classInstance = new $className();

            $params = array_merge((array) array_slice($matcher, 2, -1));

            call_user_func_array(
                [
                    $classInstance,
                    $matcher['method'].'Action'
                ],
                $params
            );

        } catch (MethodNotAllowedException|ResourceNotFoundException|Exception $e) {
           // TODO: log error;
            throw $e;
        }
    }
}