<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Room;
use App\Models\Tournament;
use App\Models\PayosPayment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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

    public function payosPayments()
    {
        return $this->hasMany(PayosPayment::class);
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
