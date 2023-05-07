<?php declare(strict_types=1);

namespace App\Models;

class Episode
{
    private int $ID;
    private string $name;
    private string $airDate;
    private string $episode;
    private array $characters;

    public function __construct(int $ID, string $name, string $airDate, string $episode, array $characters)
    {
        $this->ID = $ID;
        $this->name = $name;
        $this->airDate = $airDate;
        $this->episode = $episode;
        $this->characters = $characters;
    }

    public function getID(): int
    {
        return $this->ID;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAirDate(): string
    {
        return $this->airDate;
    }

    public function getEpisode(): string
    {
        return $this->episode;
    }

    public function getCharacters(): array
    {
        return $this->characters;
    }
}
