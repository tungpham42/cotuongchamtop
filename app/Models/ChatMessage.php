<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_code',
        'username',
        'message',
        'type',
        'ip_address'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Get messages for a specific room
    public static function getMessagesForRoom($roomCode, $limit = 50)
    {
        return self::where('room_code', $roomCode)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    // Add a new message
    public static function addMessage($roomCode, $username, $message, $type = 'message', $ipAddress = null)
    {
        return self::create([
            'room_code' => $roomCode,
            'username' => $username,
            'message' => $message,
            'type' => $type,
            'ip_address' => $ipAddress ?: request()->ip()
        ]);
    }

    // Clean old messages (keep only last 1000 per room)
    public static function cleanOldMessages($roomCode)
    {
        $messageIds = self::where('room_code', $roomCode)
            ->orderBy('created_at', 'desc')
            ->limit(1000)
            ->pluck('id');

        if ($messageIds->count() >= 1000) {
            self::where('room_code', $roomCode)
                ->whereNotIn('id', $messageIds)
                ->delete();
        }
    }
}
