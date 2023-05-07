<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\Character;
use App\Models\Episode;
use App\Views\View;
use GuzzleHttp\Client;
use Twig\Environment;

class CharacterController
{
    private Character $model;
    private Client $httpClient;

    public function __construct()
    {
        $this->model = new Character();
        $this->httpClient = new Client();
    }

    public function home(array $vars, Environment $twig): View
    {
        $characters = $this->model->getCharacters();
        array_splice($characters, 6);

        return new View('Cards', ['characters' => $characters]);
    }


    public function characterJson(array $vars, Environment $twig): View
    {
        $id = $vars['id'];
        $url = "https://rickandmortyapi.com/api/character/$id";

        $response = $this->httpClient->request('GET', $url);
        $characterData = json_decode($response->getBody()->getContents(), true);

        return new View('Json', ['data' => $characterData]);
    }

    public function locationJson(array $vars, Environment $twig): View
    {
        $id = $vars['id'];
        $url = "https://rickandmortyapi.com/api/location/$id";

        $response = $this->httpClient->request('GET', $url);
        $locationData = json_decode($response->getBody()->getContents(), true);

        return new View('Json', ['data' => $locationData]);
    }

    public function episodeObject(array $vars, Environment $twig): View
    {
        $episodeId = $vars['id'];
        $url = "https://rickandmortyapi.com/api/episode/$episodeId";

        $response = $this->httpClient->request('GET', $url);
        $episodeData = json_decode($response->getBody()->getContents(), true);

        $ID = $episodeData['id'];
        $name = $episodeData['name'];
        $airDate = $episodeData['air_date'];
        $episode = $episodeData['episode'];
        $characters = $episodeData['characters'] ?? [];

        $episode = new Episode($ID, $name, $airDate, $episode, $characters);

        return new View('EpisodeObject', ['data' => $episode]);
    }
}
