<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Google\Client as GoogleClient;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function handleOneTapCallback(Request $request)
    {
        // Because One Tap posts directly from the current page, url()->previous()
        // will naturally contain the exact page the user was looking at.
        $previousUrl = Session::get('previousUrl', url()->previous());

        // 1. Get the ID Token sent by Google
        $token = $request->input('credential'); // Google sends this field via POST

        if (!$token) {
            return Redirect::to($previousUrl)->with('error', 'No credential provided.');
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

                // 6. Redirect back to the previous page
                return Redirect::to($previousUrl)->with('success', 'Bạn đã đăng nhập bằng Google thành công!');
            } else {
                return Redirect::to($previousUrl)->with('error', 'Invalid Google Token.');
            }

        } catch (\Exception $e) {
            return Redirect::to($previousUrl)->with('error', 'Login failed: ' . $e->getMessage());
        }
    }
}
