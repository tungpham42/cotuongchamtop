<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tournament extends Model
{
    // Thêm 'cover_photo' vào mảng fillable
    protected $fillable = ['name', 'slug', 'description', 'cover_photo', 'start_date', 'status', 'max_players'];

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

    public function users()
    {
        return $this->belongsToMany(User::class, 'tournament_user')->withTimestamps();
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
