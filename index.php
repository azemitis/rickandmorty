<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

use App\Core\Router;

$routes = require_once 'routes.php';

$result = Router::run($routes);

echo $result;
