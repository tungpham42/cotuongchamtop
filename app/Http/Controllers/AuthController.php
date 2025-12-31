<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Google\Client as GoogleClient;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function handleOneTapCallback(Request $request)
    {
        // 1. Get the ID Token sent by Google
        $token = $request->input('credential'); // Google sends this field via POST

        if (!$token) {
            return redirect('/')->with('error', 'No credential provided.');
        }

        try {
            // 2. Verify the Token
            $client = new GoogleClient(['client_id' => config('services.google.client_id')]);
            $payload = $client->verifyIdToken($token);

            if ($payload) {
                // 3. Get User Info from Payload
                $googleId = $payload['sub'];
                $email = $payload['email'];
                $name = $payload['name'];
                $avatar = $payload['picture'];

                // 4. Find or Create User in Database
                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'google_id' => $googleId,
                        'avatar' => $avatar,
                        'password' => bcrypt(Str::random(16)), // Random password
                    ]
                );

                // 5. Log the user in
                Auth::login($user);

                // 6. Redirect back to home/game
                return redirect()->intended('/')->with('success', 'Logged in successfully!');
            } else {
                return redirect('/')->with('error', 'Invalid Google Token.');
            }

        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Login failed: ' . $e->getMessage());
        }
    }
}
