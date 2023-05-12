<?php declare(strict_types=1);

return [
    ['GET', '/', ['App\Controllers\HomeController', 'home']],
    ['GET', '/characters/{page:\d+}', ['App\Controllers\HomeController', 'home']],
    ['GET', '/character/{id}', ['App\Controllers\HomeController', 'characterObject']],
    ['GET', '/location/{id}', ['App\Controllers\HomeController', 'locationObject']],
    ['GET', '/episode/{id}', ['App\Controllers\HomeController', 'episodeObject']],
    ['GET', '/search', ['App\Controllers\HomeController', 'searchCharacters']],
    ['GET', '/filterLocations', ['App\Controllers\HomeController', 'filterLocations']],
    ];
