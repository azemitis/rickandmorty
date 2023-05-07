<?php declare(strict_types=1);

return [
    ['GET', '/', ['App\Controllers\CharacterController', 'home']],
    ['GET', '/character/{id}', ['App\Controllers\CharacterController', 'characterJson']],
    ['GET', '/location/{id}', ['App\Controllers\CharacterController', 'locationJson']],
    ['GET', '/episode/{id}', ['App\Controllers\CharacterController', 'episodeObject']]
];

