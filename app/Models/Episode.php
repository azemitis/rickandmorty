<?php declare(strict_types=1);

namespace App\Models;

class Episode
{
    private string $name;

    public function __construct(string $name)
    {

        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}