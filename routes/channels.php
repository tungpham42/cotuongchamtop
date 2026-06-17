<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Session;

Broadcast::channel('online', function ($user = null) {
    // If the user is logged in
    if ($user) {
        return [
            'id' => 'user_' . $user->id,
            'name' => $user->name,
        ];
    }

    // If the user is a guest, assign their Session ID as their unique ID
    return [
        'id' => 'guest_' . Session::getId(),
        'name' => 'Guest',
    ];
});
