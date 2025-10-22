<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_code',
        'parent_id',
        'user_id',
        'author_name',
        'content',
        'is_public',
        'ip_address',
        'likes_count',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_code', 'code');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('created_at');
    }

    public function likes()
    {
        return $this->hasMany(RoomCommentLike::class);
    }
}
