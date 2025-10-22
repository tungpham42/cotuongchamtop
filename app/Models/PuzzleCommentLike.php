<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PuzzleCommentLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'puzzle_comment_id',
        'user_id',
        'identifier',
        'ip_address',
    ];

    public function comment()
    {
        return $this->belongsTo(PuzzleComment::class, 'puzzle_comment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
