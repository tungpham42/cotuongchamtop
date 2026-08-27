<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KarmaLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'amount',
        'reason',
        'reference_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'integer',
        'reference_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Human-readable label for a karma reason code, used by both the
     * server (session-flashed login karma) and the API responses
     * (match karma) that drive the bootbox notifications.
     */
    public static function reasonLabel(string $reason): string
    {
        return match ($reason) {
            'every_login' => __('Đăng nhập thành công'),
            'match_played' => __('Tham gia trận đấu'),
            'match_win' => __('Chiến thắng trận đấu'),
            default => __('Karma'),
        };
    }
}
