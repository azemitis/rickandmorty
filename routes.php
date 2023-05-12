<?php declare(strict_types=1);

return [
    ['GET', '/', ['App\Controllers\CharacterController', 'home']],
    ['GET', '/characters/{page:\d+}', ['App\Controllers\CharacterController', 'home']],
    ['GET', '/character/{id}', ['App\Controllers\CharacterController', 'characterObject']],
    ['GET', '/location/{id}', ['App\Controllers\CharacterController', 'locationObject']],
    ['GET', '/episode/{id}', ['App\Controllers\CharacterController', 'episodeObject']],
    ['GET', '/search', ['App\Controllers\CharacterController', 'searchCharacters']],
    ['GET', '/filterLocations', ['App\Controllers\CharacterController', 'filterLocations']],
    ];
