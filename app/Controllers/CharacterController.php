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

                $totalCharacters = $data['info']['count'];

                Cache::remember($cacheKey, $totalCharacters, 5);
            } else {
                $totalCharacters = Cache::get($cacheKey);
            }

            $currentPage = isset($vars['page']) ? (int) $vars['page'] : 1;
            $perPage = 10;
            $totalPages = ceil($totalCharacters / $perPage);
            $offset = ($currentPage - 1) * $perPage;

            $characters = [];

            if ($offset < $totalCharacters) {
                $url = 'https://rickandmortyapi.com/api/character/?page=' . $currentPage;
                $response = $this->httpClient->request('GET', $url);
                $data = json_decode($response->getBody()->getContents(), true);

                $charactersData = $data['results'];

                foreach ($charactersData as $characterData) {
                    $episodeUrl = $characterData['episode'][0];
                    $episodeResponse = $this->httpClient->request('GET', $episodeUrl);
                    $episodeData = json_decode($episodeResponse->getBody()->getContents(), true);
                    $firstSeenIn = $episodeData['name'];
                    $firstSeenId = $episodeData['id'];

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
                        $firstSeenIn,
                        $firstSeenId,
                        $url
                    );

                    $characterCacheKey = 'character_' . $characterData['id'];
                    Cache::remember($characterCacheKey, $characterObject, 5);

                    $characters[] = $characterObject;
                }
            }

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
