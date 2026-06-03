<?php

namespace App\Models;

use App\Models\User;
use App\Models\Room;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tournament extends Model
{
    // Added 'user_id' into fillable
    protected $fillable = ['user_id', 'name', 'slug', 'description', 'cover_photo', 'start_date', 'status', 'max_players'];

    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug on creation
        static::creating(function ($tournament) {
            if (empty($tournament->slug)) {
                $tournament->slug = Str::slug($tournament->name) . '-' . Str::random(5);
            }
        });
    }

    // Relationship: The user who CREATED the tournament
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship: The users PARTICIPATING in the tournament
    public function users()
    {
        return $this->belongsToMany(User::class, 'tournament_user')->withTimestamps();
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
