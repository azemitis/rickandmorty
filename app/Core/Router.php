<?php declare(strict_types=1);

namespace app\Core;

use FastRoute\RouteCollector;

class Router
{
    public static function run(array $routes)
    {
        $dispatcher = \FastRoute\simpleDispatcher(function (RouteCollector $router) use ($routes) {
            foreach ($routes as $route)
            {
                [$httpMethod, $url, $handler] = $route;
                $router->addRoute($httpMethod, $url, $handler);
            }
        });

        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                http_response_code(404);
                return 'rabbit';
            case \FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                [$class, $method] = $handler;

                $controller = new $class();
                $loader = new \Twig\Loader\FilesystemLoader('App/Views');
                $twig = new \Twig\Environment($loader);

                return $controller->$method($vars, $twig);
        }
    }
}
