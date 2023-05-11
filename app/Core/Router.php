<?php declare(strict_types=1);

namespace App\Core;

use FastRoute\RouteCollector;
use FastRoute\Dispatcher;

class Router
{
    public static function run(array $routes)
    {
        $dispatcher = \FastRoute\simpleDispatcher(function (RouteCollector $router) use ($routes) {
            foreach ($routes as $route) {
                [$httpMethod, $url, $handler] = $route;
                $router->addRoute($httpMethod, $url, $handler);
            }
        });

        $httpMethod = $_SERVER['REQUEST_METHOD'];

        // Extract the query parameters
        $uri = $_SERVER['REQUEST_URI'];
        $queryString = parse_url($uri, PHP_URL_QUERY);
        if ($queryString) {
            parse_str($queryString, $queryParams);
            $uri = parse_url($uri, PHP_URL_PATH);
        } else {
            $queryParams = [];
        }

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);


        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                http_response_code(404);
                return 'Unknown';
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                // Check if query string is set
                if (isset($_SERVER['QUERY_STRING'])) {
                    parse_str($_SERVER['QUERY_STRING'], $queryParams);
                    $vars['queryParams'] = $queryParams;
                } else {
                    $vars['queryParams'] = [];
                }

                [$class, $method] = $handler;

                $controller = new $class();
                $loader = new \Twig\Loader\FilesystemLoader('../App/Views');
                $twig = new \Twig\Environment($loader);

                $view = $controller->$method($vars, $twig);

                $template = $twig->load($view->getTemplate() . '.twig');
                return $template->render($view->getData());
        }

        return null;
    }
}
