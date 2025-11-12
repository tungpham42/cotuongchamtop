<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Puzzle extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';

    public $fillable = [
        'name',
        'slug',
        'fen',
        'rating',
        'description',
        'is_public',
        'likes_count',
        'hard_count',
        'unsolved_count',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function comments()
    {
        return $this->hasMany(PuzzleComment::class);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public static function makeUniqueSlug(string $name, ?string $preferred = null): string
    {
        // Try preferred slug first if provided
        if ($preferred) {
            $candidate = Str::slug($preferred);
            if ($candidate && !static::where('slug', $candidate)->exists()) {
                return Str::limit($candidate, 255, '');
            }
        }

        // Generate base slug from name using Laravel's built-in methods
        $base = Str::transliterate($name);
        $base = Str::slug($base);
        $base = Str::limit($base, 240, ''); // Leave room for suffix

        // If base slug is available, use it
        if (!static::where('slug', $base)->exists()) {
            return $base;
        }

        // Generate unique slug with counter
        $counter = 1;
        do {
            $suffix = '-' . $counter;
            $candidate = Str::limit($base, 255 - strlen($suffix), '') . $suffix;
            $counter++;
        } while (static::where('slug', $candidate)->exists() && $counter <= 1000);

        return $candidate;
    }
}
