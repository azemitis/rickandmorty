<?php

namespace App\Controllers;

use App\Cache;
use App\Models\CharacterObject;
use App\Models\Episode;
use App\Models\Location;
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

    public function getAllLocations(): array
    {
        $locations = [];

        try {
            for ($i = 1; $i <= 7; $i++) {
                $url = 'https://rickandmortyapi.com/api/location?page=' . $i;
                $response = $this->httpClient->request('GET', $url);
                $data = json_decode($response->getBody()->getContents(), true);

                $locationsData = $data['results'];

                foreach ($locationsData as $locationData) {
                    $locations[] = $locationData['name'];
                }
            }
        } catch (GuzzleException $exception) {
            // Add the exceptions
        }

        return $locations;
    }

    public function getAllEpisodes(): array
    {
        $episodes = [];

        try {
            for ($i = 1; $i <= 3; $i++) {
                $url = 'https://rickandmortyapi.com/api/episode?page=' . $i;
                $response = $this->httpClient->request('GET', $url);
                $data = json_decode($response->getBody()->getContents(), true);

                $episodesData = $data['results'];

                foreach ($episodesData as $episodeData) {
                    $episodes[] = $episodeData['name'];
                }
            }
        } catch (GuzzleException $exception) {
            // Add exceptions
        }

        return $episodes;
    }


    public function home(array $vars, Environment $twig): View
    {
        try {
            $cacheKey = 'characters';

            $locations = $this->getAllLocations();
            $episodes = $this->getAllEpisodes();

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

            return new View('Cards', [
                'characters' => $paginatedCharacters,
                'pagination' => $pagination,
                'locations' => $locations,
                'episodes' => $episodes,
                ]);
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

    public function searchCharacters(array $vars, Environment $twig): View
    {
        $query = $_GET['q'];

        try {
            $filteredCharacters = [];

            for ($i = 1; $i <= 42; $i++) {
                $url = 'https://rickandmortyapi.com/api/character?page=' . $i;
                $response = $this->httpClient->request('GET', $url);
                $data = json_decode($response->getBody()->getContents(), true);

                $characters = $data['results'];

                foreach ($characters as $character) {
                    if (stripos($character['name'], $query) !== false) {
                        $filteredCharacters[] = $character;
                    }
                }
            }

            $locations = $this->getAllLocations();
            $episodes = $this->getAllEpisodes();

            return new View('SearchResults', [
                'characters' => $filteredCharacters,
                'locations' => $locations,
                'episodes' => $episodes
            ]);
        } catch (GuzzleException $exception) {
            $errorMessage = 'Error fetching character data.';
            return new View('Message', ['message' => $errorMessage]);
        }
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

    public function locationObject(array $vars, Environment $twig): View
    {
        $id = $vars['id'];
        $url = "https://rickandmortyapi.com/api/location/$id";

        $cacheKey = 'location_' . $id;

        if (!Cache::has($cacheKey)) {
            try {
                $response = $this->httpClient->request('GET', $url);
                $locationData = json_decode($response->getBody()->getContents(), true);

                $ID = $locationData['id'];
                $name = $locationData['name'];
                $type = $locationData['type'];
                $dimension = $locationData['dimension'];
                $residents = $locationData['residents'];

                $location = new Location($ID, $name, $type, $dimension, $residents);

                Cache::remember($cacheKey, $location, 15);

                $this->fetchMessage = 'Data from external API received.';
            } catch (RequestException $exception) {
                $errorMessage = 'Error fetching location data.';
                return new View('Message', ['message' => $errorMessage]);
            }
        } else {
            $location = Cache::get($cacheKey);
        }

        return new View('Location', ['data' => $location]);
    }

    public function episodeObject(array $vars, Environment $twig): View
    {
        $episodeId = $vars['id'];
        $url = "https://rickandmortyapi.com/api/episode/$episodeId";

        $cacheKey = 'episode_' . $episodeId;

        if (!Cache::has($cacheKey)) {
            try {
                $response = $this->httpClient->request('GET', $url);
                $episodeData = json_decode($response->getBody()->getContents(), true);

                $ID = $episodeData['id'];
                $name = $episodeData['name'];
                $airDate = $episodeData['air_date'];
                $episode = $episodeData['episode'];
                $characters = $episodeData['characters'];

                $episode = new Episode($ID, $name, $airDate, $episode, $characters);

                Cache::remember($cacheKey, $episode, 15);

                $this->fetchMessage = 'Data from external API received.';
            } catch (RequestException $exception) {
                $errorMessage = 'Error fetching episode data.';
                return new View('Message', ['message' => $errorMessage]);
            }
        } else {
            $episode = Cache::get($cacheKey);
        }

        return new View('Episode', ['data' => $episode]);
    }
}