<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;
    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;
    
    public $fillable = [
        'code',
        'fen',
        'result',
        'name',
        'host_id',
        'guest_id',
        'host_session',
        'guest_session',
        'pass',
        'modified_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function guest()
    {
        return $this->belongsTo(User::class, 'guest_id');
    }

    public function hostTimeRemaining($seconds)
    {
        $this->host_time_remaining = $seconds;
        $this->save();
    }

    public function guestTimeRemaining($seconds)
    {
        $this->guest_time_remaining = $seconds;
        $this->save();
    }

    /**
     * Find an open room for anonymous matchmaking (has host but no guest).
     *
     * @return \App\Models\Room|null
     */
    public static function findOpenAnonymousRoom()
    {
        return self::whereNotNull('host_session')
            ->whereNull('guest_session')
            ->whereNull('result')
            ->whereNull('pass')
            ->where('fen', env('INITIAL_FEN'))
            ->where('modified_at', '>', now()->subMinutes(5)) // Only recent rooms
            ->orderBy('modified_at', 'asc') // First come first serve
            ->first();
    }
}
