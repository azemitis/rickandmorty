<?php declare(strict_types=1);

namespace App\Models;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Character
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function getCharacters(): array
    {
        try {
            $url = 'https://rickandmortyapi.com/api/character/';

            $response = $this->client->request('GET', $url);

            $data = json_decode($response->getBody()->getContents(), true);

            $characters = [];

            foreach ($data['results'] as $character) {
                $episodeUrl = $character['episode'][0];
                $episodeResponse = $this->client->request('GET', $episodeUrl);
                $episodeData = json_decode($episodeResponse->getBody()->getContents(), true);
                $firstSeenIn = $episodeData['name'];

                $locationUrl = $character['location']['url'];
                $locationId = substr($locationUrl, strrpos($locationUrl, '/') + 1);

                $characters[] = [
                    'title' => $character['name'],
                    'status' => $character['status'],
                    'species' => $character['species'],
                    'image' => $character['image'],
                    'id' => $character['id'],
                    'location_id' => $locationId,
                    'last_known_location' => $character['location']['name'],
                    'first_seen_in' => $firstSeenIn,
                    'first_seen_id' => $episodeData['id'],
                    'url' => $url,
                ];
            }

            return $characters;

        } catch (GuzzleException $exception) {
            return [];
        }
    }

}