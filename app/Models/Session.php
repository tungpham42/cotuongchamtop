<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    // Point to the correct table
    protected $table = 'sessions';

    // The sessions table uses a string ID, not an auto-incrementing integer
    public $incrementing = false;
    protected $keyType = 'string';

    // Disable standard created_at/updated_at timestamps
    public $timestamps = false;

    protected $fillable = [
        'id',
        'user_id',
        'ip_address',
        'user_agent',
        'payload',
        'last_activity'
    ];
}
