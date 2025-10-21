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
        $candidate = Str::slug((string) $preferred);
        if ($candidate && !static::where('slug', $candidate)->exists()) {
            return Str::limit($candidate, 255, '');
        }

        $base = Str::slug($name) ?: 'the-co';
        $base = Str::limit($base, 190, '');

        do {
            $prefix = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $candidate = Str::limit($prefix.'-'.$base, 255, '');
        } while (static::where('slug', $candidate)->exists());

        return $candidate;
    }
}
