<?php

namespace App\Controllers;

use App\Cache;
use App\Models\CharacterObject;
use App\Models\Episode;
use App\Views\View;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Twig\Environment;

class CharacterController
{
    private Client $httpClient;
    private string $fetchMessage = '';

    public function __construct()
    {
        $this->httpClient = new Client();
    }

//    Message if external API data is used
    private function logFetchedData(array $data): void
    {
        $this->fetchMessage = 'Data from external API received.';
    }

    public function home(array $vars, Environment $twig): View
    {
        try {
            $cacheKey = 'characters';

            if (!Cache::has($cacheKey)) {
                $url = 'https://rickandmortyapi.com/api/character/';
                $response = $this->httpClient->request('GET', $url);
                $data = json_decode($response->getBody()->getContents(), true);

                $totalCharacters = $data['info']['count'];

                Cache::remember($cacheKey, $totalCharacters, 15);
            } else {
                $totalCharacters = Cache::get($cacheKey);
            }

            $currentPage = isset($vars['page']) ? (int)$vars['page'] : 1;

            $characters = $this->fetchCharacters($currentPage);

            $perPage = count($characters);
            $totalPages = ceil($totalCharacters / $perPage);

            $paginatedCharacters = $characters;

            $pagination = [
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
            ];

            if ($currentPage > 1) {
                $prevPage = $currentPage - 1;
                $pagination['prev_url'] = "/characters/$prevPage";
            }

            if ($currentPage < $totalPages) {
                $nextPage = $currentPage + 1;
                $pagination['next_url'] = "/characters/$nextPage";
            }

            return new View('Cards', ['characters' => $paginatedCharacters, 'pagination' => $pagination]);
        } catch (GuzzleException $exception) {
            $errorMessage = 'Error fetching character data.';
            return new View('Message', ['message' => $errorMessage]);
        }
    }

    private function fetchCharacters(int $currentPage): array
    {
        $characters = [];

        $url = 'https://rickandmortyapi.com/api/character/?page=' . $currentPage;
        $response = $this->httpClient->request('GET', $url);
        $data = json_decode($response->getBody()->getContents(), true);

        $charactersData = $data['results'];

        foreach ($charactersData as $characterData) {
            $episodeUrl = $characterData['episode'][0];
            $episodeResponse = $this->httpClient->request('GET', $episodeUrl);
            $episodeData = json_decode($episodeResponse->getBody()->getContents(), true);

            $locationUrl = $characterData['location']['url'];
            $locationId = substr($locationUrl, strrpos($locationUrl, '/') + 1);

            $characterObject = new CharacterObject(
                $characterData['id'],
                $characterData['name'],
                $characterData['status'],
                $characterData['species'],
                $characterData['image'],
                $locationId,
                $characterData['location']['name'],
                $episodeData['name'],
                $episodeData['id'],
                $url
            );

            $characterCacheKey = 'character_' . $characterData['id'];
            Cache::remember($characterCacheKey, $characterObject, 15);

            $characters[] = $characterObject;
        }

        return $characters;
    }

    public function characterObject(array $vars, Environment $twig): View
    {
        $id = $vars['id'];
        $url = "https://rickandmortyapi.com/api/character/$id";

        $cacheKey = 'character_' . $id;

        if (!Cache::has($cacheKey)) {
            try {
                $response = $this->httpClient->request('GET', $url);
                $characterData = json_decode($response->getBody()->getContents(), true);

                $locationUrl = $characterData['location']['url'];
                $locationResponse = $this->httpClient->request('GET', $locationUrl);
                $locationData = json_decode($locationResponse->getBody()->getContents(), true);

                $episodeUrl = $characterData['episode'][0];
                $episodeResponse = $this->httpClient->request('GET', $episodeUrl);
                $episodeData = json_decode($episodeResponse->getBody()->getContents(), true);

                $locationId = substr($locationUrl, strrpos($locationUrl, '/') + 1);

                $characterObject = new CharacterObject(
                    $characterData['id'],
                    $characterData['name'],
                    $characterData['status'],
                    $characterData['species'],
                    $characterData['image'],
                    $locationId,
                    $locationData['name'],
                    $episodeData['name'],
                    $episodeData['id'],
                    $url
                );

                Cache::remember($cacheKey, $characterObject, 15);

                $this->fetchMessage = 'Data from external API received.';
            } catch (RequestException $exception) {
                $errorMessage = 'Error fetching character data.';
                return new View('Message', ['message' => $errorMessage]);
            }
        } else {
            $characterObject = Cache::get($cacheKey);
        }

        return new View('Character', [
            'character' => $characterObject,
            'fetchMessage' => $this->fetchMessage
        ]);
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

        if (!Cache::has('episode')) {
            var_dump('ask rick and morty');
            $response = $this->httpClient->request('GET', $url);
            $responseJson = $response->getBody()->getContents();
            Cache::remember('episode', $responseJson, 15);
        } else {
            var_dump('ask cache');
            $responseJson = Cache::get('episode');
        }

        $episodeData = json_decode($responseJson, true);

        $ID = $episodeData['id'];
        $name = $episodeData['name'];
        $airDate = $episodeData['air_date'];
        $episode = $episodeData['episode'];
        $characters = $episodeData['characters'];

        $episode = new Episode($ID, $name, $airDate, $episode, $characters);

        return new View('EpisodeObject', ['data' => $episode]);
    }
}
