<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Pusher\Pusher;

class BroadcastAuthController extends Controller
{
    public function authenticate(Request $request)
    {
        // Reverb speaks the Pusher protocol, so we still use the Pusher SDK —
        // just pointed at Reverb's credentials via config(), not env().
        $pusher = new Pusher(
            config('broadcasting.connections.reverb.key'),
            config('broadcasting.connections.reverb.secret'),
            config('broadcasting.connections.reverb.app_id'),
            [
                'cluster' => '', // Reverb doesn't use Pusher clusters
                'useTLS' => true,
                'host' => config('broadcasting.connections.reverb.options.host'),
                'port' => config('broadcasting.connections.reverb.options.port'),
                'scheme' => config('broadcasting.connections.reverb.options.scheme'),
            ]
        );

        $channelName = $request->channel_name;
        $socketId = $request->socket_id;

        $user = auth()->user();

        if ($user) {
            $userId = 'user_' . $user->id;
            $userInfo = ['name' => $user->name];
        } else {
            $userId = 'guest_' . session()->getId();
            $userInfo = ['name' => 'Guest'];
        }

        $auth = $pusher->presence_auth($channelName, $socketId, $userId, $userInfo);

        return response($auth)->header('Content-Type', 'application/json');
    }
}
