<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\Rick;
use GuzzleHttp\Client;
use Twig\Environment;

class RickController
{
    private Rick $model;
    private Client $httpClient;

    public function __construct()
    {
        $this->model = new Rick();
        $this->httpClient = new Client();
    }

    public function home(array $vars, Environment $twig): string
    {
        $characters = $this->model->getRicks();
        array_splice($characters, 10);

        $template = $twig->load('Cards.twig');

        return $template->render([
            'characters' => $characters,
        ]);
    }


    public function characterJson(array $vars, Environment $twig): string
    {
        $id = $vars['id'];
        $url = "https://rickandmortyapi.com/api/character/$id";

        $response = $this->httpClient->request('GET', $url);
        $characterData = json_decode($response->getBody()->getContents(), true);

        $template = $twig->load('Json.twig');

        return $template->render([
            'data' => $characterData,
        ]);
    }

    public function locationJson(array $vars, Environment $twig): string
    {
        $id = $vars['id'];
        $url = "https://rickandmortyapi.com/api/location/$id";

        $response = $this->httpClient->request('GET', $url);
        $locationData = json_decode($response->getBody()->getContents(), true);

        $template = $twig->load('Json.twig');

        return $template->render([
            'data' => $locationData,
        ]);
    }

    public function episodeJson(array $vars, Environment $twig): string
    {
        $id = $vars['id'];
        $url = "https://rickandmortyapi.com/api/episode/$id";

        $response = $this->httpClient->request('GET', $url);
        $episodeData = json_decode($response->getBody()->getContents(), true);

        $template = $twig->load('Json.twig');

        return $template->render([
            'data' => $episodeData,
        ]);
    }
}
