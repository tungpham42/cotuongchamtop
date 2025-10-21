<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PuzzleComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'puzzle_id',
        'parent_id',
        'user_id',
        'author_name',
        'content',
        'is_public',
        'ip_address',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function puzzle()
    {
        return $this->belongsTo(Puzzle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('created_at');
    }
}
