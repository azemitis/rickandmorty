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

        try {
            $episodeUrl = $characterData['episode'][0];
            $episodeResponse = $this->httpClient->request('GET', $episodeUrl);
            $episodeData = json_decode($episodeResponse->getBody()->getContents(), true);
            $firstSeenIn = new Episode($episodeData['name']);
        } catch (\Exception $e) {
            $firstSeenIn = null;
        }

        return new View('Json', ['data' => $characterData, 'firstSeenIn' => $firstSeenIn]);
    }




    public function locationJson(array $vars, Environment $twig): View
    {
        $id = $vars['id'];
        $url = "https://rickandmortyapi.com/api/location/$id";

        $response = $this->httpClient->request('GET', $url);
        $locationData = json_decode($response->getBody()->getContents(), true);

        return new View('Json', ['data' => $locationData]);
    }


    public function episodeJson(array $vars, Environment $twig): View
    {
        $id = $vars['id'];
        $url = "https://rickandmortyapi.com/api/episode/$id";

        $response = $this->httpClient->request('GET', $url);
        $episodeData = json_decode($response->getBody()->getContents(), true);

        return new View('Json', ['data' => $episodeData]);
    }
}
