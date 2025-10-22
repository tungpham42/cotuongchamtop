<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomCommentLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_comment_id',
        'user_id',
        'identifier',
        'ip_address',
    ];

    public function comment()
    {
        return $this->belongsTo(RoomComment::class, 'room_comment_id');
    }
}
