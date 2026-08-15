<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Room extends Model
{
    use HasFactory;

    protected $primaryKey = 'code';
    protected $keyType = 'string';
    public $incrementing = false;
    public const INITIAL_FEN = 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1';

    public $fillable = [
        'code', 'fen', 'moves', 'result', 'name', 'host_id', 'guest_id',
        'host_session', 'guest_session', 'pass', 'red_time', 'black_time',
        'active_player', 'last_update', 'modified_at', 'tournament_id',
        'tournament_round', 'next_room_code', 'host_score', 'guest_score', 'host_elo', 'guest_elo'
    ];

    // --- Relationships ---

    public function host() { return $this->belongsTo(User::class, 'host_id'); }
    public function guest() { return $this->belongsTo(User::class, 'guest_id'); }
    public function tournament() { return $this->belongsTo(Tournament::class); }

    // --- Scopes (For clean queries) ---

    public function scopeOngoing($query)
    {
        return $query->whereNull('result')
            ->where(fn($q) => $q->whereNotNull('host_id')->orWhereNotNull('host_session'))
            ->where(fn($q) => $q->whereNotNull('guest_id')->orWhereNotNull('guest_session'));
    }

    public function scopeAvailableForAnonymousMatch($query, $fen)
    {
        return $query->whereNotNull('host_session')
            ->whereNull('guest_session')
            ->whereNull('result')
            ->whereNull('pass')
            ->where('fen', $fen)
            ->where('modified_at', '>', now()->subSeconds(15))
            ->orderByDesc('modified_at');
    }

    // --- Business Logic (Timers & State) ---

    public function hasTimedOut(): bool
    {
        if (!$this->active_player || $this->active_player === 'waiting' || str_starts_with($this->active_player, 'paused:')) {
            return false;
        }

        $elapsed = $this->getSecondsSinceLastUpdate();
        $moveElapsed = $this->getBufferedMoveElapsed() + $elapsed;

        if ($this->active_player === 'red') {
            return ($moveElapsed >= 120 || (float) $this->red_time - $elapsed <= 0);
        }

        return ($moveElapsed >= 120 || (float) $this->black_time - $elapsed <= 0);
    }

    public function processTimeout(): bool
    {
        if ($this->active_player === 'red') {
            return $this->update(['result' => '-1', 'modified_at' => now()]);
        }
        if ($this->active_player === 'black') {
            return $this->update(['result' => '1', 'modified_at' => now()]);
        }
        return false;
    }

    public function switchTurn(string $currentPlayer): void
    {
        $elapsed = $this->getSecondsSinceLastUpdate();
        $totalMoveElapsed = $this->getBufferedMoveElapsed() + $elapsed;

        if ($elapsed > 0) {
            if ($currentPlayer === 'red') {
                $this->red_time = $totalMoveElapsed >= 120 ? 0 : max(0, (float)$this->red_time - $elapsed);
            } else {
                $this->black_time = $totalMoveElapsed >= 120 ? 0 : max(0, (float)$this->black_time - $elapsed);
            }
        }

        Cache::forget("room_{$this->code}_move_elapsed");

        $this->active_player = $currentPlayer === 'red' ? 'black' : 'red';
        $this->last_update = now();
        $this->modified_at = now();
        $this->save();
    }

    public function getCalculatedTimes(): array
    {
        $redTime = (float) $this->red_time;
        $blackTime = (float) $this->black_time;
        $moveElapsed = $this->getBufferedMoveElapsed();

        $activePlayer = str_starts_with($this->active_player ?? '', 'paused:')
            ? explode(':', $this->active_player)[1]
            : $this->active_player;

        if ($this->active_player && !str_starts_with($this->active_player, 'paused:')) {
            $elapsed = $this->getSecondsSinceLastUpdate();
            $moveElapsed += $elapsed;

            if ($activePlayer === 'red') {
                $redTime = max(0, $redTime - $elapsed);
                if ($moveElapsed >= 120) $redTime = 0;
            } elseif ($activePlayer === 'black') {
                $blackTime = max(0, $blackTime - $elapsed);
                if ($moveElapsed >= 120) $blackTime = 0;
            }
        }

        return [
            'red_time'      => round($redTime, 3),
            'black_time'    => round($blackTime, 3),
            'move_elapsed'  => round($moveElapsed, 3),
            'active_player' => $this->active_player,
        ];
    }

    // --- Helpers ---

    private function getSecondsSinceLastUpdate(): float
    {
        $lastUpdate = $this->last_update ? Carbon::parse($this->last_update) : now();
        return $lastUpdate->diffInMilliseconds(now()) / 1000;
    }

    private function getBufferedMoveElapsed(): float
    {
        return (float) Cache::get("room_{$this->code}_move_elapsed", 0);
    }
}
