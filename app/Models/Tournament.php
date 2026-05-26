<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    protected $fillable = ['name', 'description', 'start_date', 'status', 'max_players'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'tournament_user')->withTimestamps();
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
