<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\Session as DbSession;
use App\Events\PlayersUpdated;
use Maicol07\SSO\Flarum;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Dynamically determines the redirect path after login.
     */
    public function redirectTo()
    {
        $locale = app()->getLocale();
        $localizedHome = ($locale === 'vi') ? '/' : '/' . $locale;

        return Session::get('previousUrl', $localizedHome);
    }

    /**
     * Helper to store the previous URL prior to logging in
     */
    private function storePreviousUrl()
    {
        if (!str_contains(url()->previous(), 'login')) {
            Session::put('previousUrl', url()->previous());
        }
    }

    public function showLoginForm()
    {
        $this->storePreviousUrl();

        // Added localized_page_data helper for hreflang tags
        return view('auth.login', localized_page_data('login', app()->getLocale()));
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // Trigger real-time UI update when a user logs in
            // broadcast(new PlayersUpdated());

            // --- BẮT ĐẦU: ĐỒNG BỘ LOGIN SANG FLARUM ---
            try {
                $flarum = new Flarum([
                    'url' => env('FLARUM_URL'),
                    'root_domain' => env('FLARUM_ROOT_DOMAIN'),
                    'api_key' => env('FLARUM_API_KEY'),
                    'remember' => true,
                ]);
                $user = Auth::user();
                $flarum->login($user->name, $user->email);
            } catch (\Exception $e) {
                Log::error('Flarum Login Error: ' . $e->getMessage());
            }
            // --- KẾT THÚC ---

            return Redirect::to($this->redirectTo());
        }

        return back()->withErrors([
            'email' => __('Thông tin đăng nhập được cung cấp không khớp với hồ sơ của chúng tôi.'),
        ]);
    }

    public function logout(Request $request)
    {
        // Capture the user ID before they are logged out
        $userId = Auth::id();

        if ($userId) {
            // Instantly delete all lingering database sessions for this user across all devices/tabs
            DbSession::where('user_id', $userId)->delete();
        }

        // --- BẮT ĐẦU: ĐỒNG BỘ LOGOUT SANG FLARUM ---
        try {
            $flarum = new Flarum([
                'url' => env('FLARUM_URL'),
                'root_domain' => env('FLARUM_ROOT_DOMAIN'),
                'api_key' => env('FLARUM_API_KEY'),
            ]);
            $flarum->logout();
        } catch (\Exception $e) {
            Log::error('Flarum Logout Error: ' . $e->getMessage());
        }
        // --- KẾT THÚC ---

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $locale = app()->getLocale();
        $localizedHome = ($locale === 'vi') ? '/' : '/' . $locale;

        $previousUrl = url()->previous() && url()->previous() !== url('/logout')
            ? url()->previous()
            : $localizedHome;

        return Redirect::to($previousUrl)->with('success', __('Bạn đã đăng xuất thành công!'));
    }

    /*
    |--------------------------------------------------------------------------
    | Socialite Methods (Refactored & Consolidated)
    |--------------------------------------------------------------------------
    */

    private function redirectProvider($driver)
    {
        $this->storePreviousUrl();
        return Socialite::driver($driver)->redirect();
    }

    private function handleProviderCallback($driver, $providerName)
    {
        $socialUser = Socialite::driver($driver)->user();
        $redirectUrl = $this->redirectTo();

        $user = null;

        if ($driver === 'zalo' && null !== $socialUser->getId()) {
            $user = User::firstOrCreate(
                ['name' => $socialUser->getName()],
                ['email' => md5(time()).'.zalo@cotuong.top']
            );
        } elseif (null !== $socialUser->getEmail()) {
            $user = User::firstOrCreate(
                ['email' => $socialUser->getEmail()],
                ['name' => $socialUser->getName()]
            );
        }

        if ($user) {
            Auth::login($user, true);
            broadcast(new \App\Events\PlayersUpdated());

            // --- BẮT ĐẦU: ĐỒNG BỘ SOCIAL LOGIN SANG FLARUM ---
            try {
                $flarum = new Flarum([
                    'url' => env('FLARUM_URL'),
                    'root_domain' => env('FLARUM_ROOT_DOMAIN'),
                    'api_key' => env('FLARUM_API_KEY'),
                    'remember' => true,
                ]);
                $flarum->login($user->name, $user->email);
            } catch (\Exception $e) {
                Log::error('Flarum Social Login Error: ' . $e->getMessage());
            }
            // --- KẾT THÚC ---

            return Redirect::to($redirectUrl)->with('success', __("Bạn đã đăng nhập bằng {$providerName} thành công!"));
        }

        return Redirect::to($redirectUrl)->withErrors(['message' => __('Tài khoản của bạn không hợp lệ hoặc thiếu email.')]);
    }

    // Facebook
    public function redirectToFacebook() { return $this->redirectProvider('facebook'); }
    public function handleFacebookCallback() { return $this->handleProviderCallback('facebook', 'Facebook'); }

    // Google
    public function redirectToGoogle() { return $this->redirectProvider('google'); }
    public function handleGoogleCallback() { return $this->handleProviderCallback('google', 'Google'); }

    // GitHub
    public function redirectToGithub() { return $this->redirectProvider('github'); }
    public function handleGithubCallback() { return $this->handleProviderCallback('github', 'GitHub'); }

    // LinkedIn
    public function redirectToLinkedin() { return $this->redirectProvider('linkedin'); }
    public function handleLinkedinCallback() { return $this->handleProviderCallback('linkedin', 'LinkedIn'); }

    // GitLab
    public function redirectToGitlab() { return $this->redirectProvider('gitlab'); }
    public function handleGitlabCallback() { return $this->handleProviderCallback('gitlab', 'GitLab'); }

    // Bitbucket
    public function redirectToBitbucket() { return $this->redirectProvider('bitbucket'); }
    public function handleBitbucketCallback() { return $this->handleProviderCallback('bitbucket', 'Bitbucket'); }

    // Zalo
    public function redirectToZalo() { return $this->redirectProvider('zalo'); }
    public function handleZaloCallback() { return $this->handleProviderCallback('zalo', 'Zalo'); }
}
