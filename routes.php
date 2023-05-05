<?php declare(strict_types=1);

//use App\Controllers\RickController;

return [
    ['GET', '/', ['App\Controllers\RickController', 'home']],
    ['GET', '/character/{id}', ['App\Controllers\RickController', 'characterJson']],
    ['GET', '/location/{id}', ['App\Controllers\RickController', 'locationJson']],
    ['GET', '/episode/{id}', ['App\Controllers\RickController', 'episodeJson']]
];

