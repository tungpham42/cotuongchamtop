<?php

namespace App\Contracts;

use App\Models\Tournament;
use Illuminate\Database\Eloquent\Collection;

interface BracketGeneratorInterface
{
    public function generate(Tournament $tournament, Collection $players): void;
}
