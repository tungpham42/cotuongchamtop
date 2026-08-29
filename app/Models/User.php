<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Room;
use App\Models\Tournament;
use App\Models\PayosPayment;
use App\Models\KarmaLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Avatar;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'points',
        'email',
        'profile_picture',
        'password',
        'is_admin',
        'subscription_plan',
        'subscription_started_at',
        'subscription_ends_at',
        'ads_removed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'subscription_started_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'ads_removed' => 'boolean',
        'is_admin' => 'boolean',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class, 'id');
    }

    public function tournaments()
    {
        return $this->belongsToMany(Tournament::class, 'tournament_user')->withTimestamps();
    }

    public function createdTournaments()
    {
        return $this->hasMany(Tournament::class, 'user_id');
    }

    /**
     * Get the URL of the user's profile picture or fallback to Avatar
     */
    public function getAvatarUrl($size = 48, $fontSize = 24): string
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }
        return Avatar::create($this->name)->setDimension($size)->setFontSize($fontSize)->toBase64();
    }

    public function payosPayments()
    {
        return $this->hasMany(PayosPayment::class);
    }

    public function karmaLogs()
    {
        return $this->hasMany(KarmaLog::class);
    }

    /**
     * Gems are not stored on the user — they're the running total of
     * this user's KarmaLog amounts. When the caller has eager-loaded
     * the sum (e.g. User::withSum('karmaLogs as gems_sum', 'amount')),
     * that value is used; otherwise it's computed on demand.
     */
    public function getGemsAttribute(): int
    {
        if (array_key_exists('gems_sum', $this->attributes)) {
            return (int) $this->attributes['gems_sum'];
        }

        return (int) $this->karmaLogs()->sum('amount');
    }

    public function setRole($role)
    {
        $this->attributes['role'] = $role;
    }

    public function getIsOnlineAttribute()
    {
        return $this->last_seen_at->diffInMinutes(now()) < 5;
    }

    public function isStandard(): bool
    {
        if ($this->subscription_plan !== 'standard') {
            return false;
        }

        if ($this->subscription_ends_at instanceof Carbon && $this->subscription_ends_at->isPast()) {
            return false;
        }

        return true;
    }

    public function hasAdsRemoved(): bool
    {
        return $this->ads_removed || $this->isStandard();
    }

    public function activateStandard(?Carbon $endsAt = null): void
    {
        $this->forceFill([
            'subscription_plan' => 'standard',
            'subscription_started_at' => now(),
            'subscription_ends_at' => $endsAt,
            'ads_removed' => true,
        ])->save();
    }
}