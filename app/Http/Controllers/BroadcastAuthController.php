<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Pusher\Pusher;

class BroadcastAuthController extends Controller
{
    public function authenticate(Request $request)
    {
        // Initialize Pusher manually using Laravel's config(), NOT env()
        $pusher = new Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            [
                'cluster' => config('broadcasting.connections.pusher.options.cluster', 'ap1'),
                'useTLS'  => true,
            ]
        );

        $channelName = $request->channel_name;
        $socketId = $request->socket_id;

        $user = auth()->user();

        if ($user) {
            $userId = 'user_' . $user->id;
            $userInfo = ['name' => $user->name];
        } else {
            // Treat the guest as a user with their session ID
            $userId = 'guest_' . session()->getId();
            $userInfo = ['name' => 'Guest'];
        }

        // Generate the authentication signature
        $auth = $pusher->presence_auth($channelName, $socketId, $userId, $userInfo);

        // FIX: Must explicitly return as application/json, otherwise Pusher rejects it
        return response($auth)->header('Content-Type', 'application/json');
    }
}
