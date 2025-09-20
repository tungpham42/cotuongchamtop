<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Room;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TimerController extends Controller
{
    public function update(Request $request)
    {
        $code = $request->input('ma-phong');

        // Get the current room from the database (assuming you have a Room model)
        $room = Room::where('code', '=', $code)->first();

        // Calculate the remaining time for the current player
        $remaining_time = $room->player_turn === 'host' ? $room->host_time_remaining : $room->guest_time_remaining;

        // Subtract the elapsed time since the last update
        $elapsed_time = now()->diffInSeconds($room->modified_at);
        $remaining_time -= $elapsed_time;

        // Update the remaining time in the database
        if ($room->player_turn === 'host') {
            $room->host_time_remaining = $remaining_time;
        } else if ($room->player_turn === 'guest') {
            $room->guest_time_remaining = $remaining_time;
        }
        $room->modified_at = now();
        $room->save();

        // Return the updated remaining time as JSON
        return response()->json([
            'remaining_time' => $remaining_time
        ]);
    }
}
