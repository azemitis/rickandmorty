<?php declare(strict_types=1);

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

    public function __construct()
    {
        $this->httpClient = new Client();
    }

    public function home(array $vars, Environment $twig): View
    {
        try {
            $cacheKey = 'characters';

            if (!Cache::has($cacheKey)) {
                $url = 'https://rickandmortyapi.com/api/character/';
                $response = $this->httpClient->request('GET', $url);
                $data = json_decode($response->getBody()->getContents(), true);

                $characters = $data['results'];
                $characters = array_slice($characters, 0, 18); // this line creates characters.json in cache
                                                            // AND BREAKS PAGINATION

                $characterObjects = [];

                foreach ($characters as $character) {
                    $episodeUrl = $character['episode'][0];
                    $episodeResponse = $this->httpClient->request('GET', $episodeUrl);
                    $episodeData = json_decode($episodeResponse->getBody()->getContents(), true);
                    $firstSeenIn = $episodeData['name'];
                    $firstSeenId = $episodeData['id'];

                    $locationUrl = $character['location']['url'];
                    $locationId = substr($locationUrl, strrpos($locationUrl, '/') + 1);

                    $characterObject = new CharacterObject(
                        $character['id'],
                        $character['name'],
                        $character['status'],
                        $character['species'],
                        $character['image'],
                        $locationId,
                        $character['location']['name'],
                        $firstSeenIn,
                        $firstSeenId,
                        $url
                    );

                    $characterObjects[] = $characterObject;

                    $characterCacheKey = 'character_' . $character['id'];
                    Cache::remember($characterCacheKey, $characterObject, 5);
                }

                Cache::remember($cacheKey, $characterObjects, 5);
            } else {
                $characterObjects = Cache::get($cacheKey);
            }

            $currentPage = isset($vars['page']) ? (int)$vars['page'] : 1;
            $perPage = 10;
            $totalCharacters = count($characterObjects);
            $totalPages = ceil($totalCharacters / $perPage);
            $offset = ($currentPage - 1) * $perPage;
            $paginatedCharacters = array_slice($characterObjects, $offset, $perPage);

            $pagination = [
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
            ];

            return new View('Cards', ['characters' => $paginatedCharacters, 'pagination' => $pagination]);
        } catch (GuzzleException $exception) {
            $errorMessage = 'Error while fetching character data.';
            return new View('Message', ['message' => $errorMessage]);
        }
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
