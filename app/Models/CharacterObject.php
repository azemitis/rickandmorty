<?php declare(strict_types=1);

namespace App\Models;

class CharacterObject
{
    public int $id;
    public string $title;
    public string $status;
    public string $species;
    public string $image;
    public string $locationId;
    public string $lastKnownLocation;
    public string $firstSeenIn;
    public int $firstSeenId;
    public string $url;

    public function __construct(
        int $id,
        string $title,
        string $status,
        string $species,
        string $image,
        string $locationId,
        string $lastKnownLocation,
        string $firstSeenIn,
        int $firstSeenId,
        string $url
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->status = $status;
        $this->species = $species;
        $this->image = $image;
        $this->locationId = $locationId;
        $this->lastKnownLocation = $lastKnownLocation;
        $this->firstSeenIn = $firstSeenIn;
        $this->firstSeenId = $firstSeenId;
        $this->url = $url;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getSpecies(): string
    {
        return $this->species;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getLocationId(): string
    {
        return $this->locationId;
    }

    public function getLastKnownLocation(): string
    {
        return $this->lastKnownLocation;
    }

    public function getFirstSeenIn(): string
    {
        return $this->firstSeenIn;
    }

    public function getFirstSeenId(): int
    {
        return $this->firstSeenId;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
