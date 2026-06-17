<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Events\PlayersUpdated;

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
            // Auth::attempt handles login, no need to query User again
            return Redirect::to($this->redirectTo());
        }

        return back()->withErrors([
            'email' => __('Thông tin đăng nhập được cung cấp không khớp với hồ sơ của chúng tôi.'),
        ]);
    }

    public function logout(Request $request)
    {
        // 1. Log the user out
        Auth::logout();

        // 2. Invalidate the session and regenerate the CSRF token for security
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 3. Determine the localized home page as a fallback
        $locale = app()->getLocale();
        $localizedHome = ($locale === 'vi') ? '/' : '/' . $locale;

        // 4. Get the URL they clicked logout from (ignore if it's the logout route itself)
        $previousUrl = url()->previous() && url()->previous() !== url('/logout')
            ? url()->previous()
            : $localizedHome;

        broadcast(new PlayersUpdated());

        // 5. Redirect with success message
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

        // Zalo has unique logic based on your original implementation
        if ($driver === 'zalo') {
            if (null !== $socialUser->getId()) {
                $user = User::firstOrCreate(
                    ['name' => $socialUser->getName()],
                    ['email' => md5(time()).'.zalo@cotuong.top']
                );

                Auth::login($user, true);
                return Redirect::to($redirectUrl)->with('success', __("Bạn đã đăng nhập bằng {$providerName} thành công!"));
            }
            return Redirect::to($redirectUrl)->withErrors(['message' => __('Tài khoản của bạn không hợp lệ.')]);
        }

        // Standard logic for all other providers (Facebook, Google, GitHub, etc.)
        if (null !== $socialUser->getEmail()) {
            $user = User::firstOrCreate(
                ['email' => $socialUser->getEmail()],
                ['name' => $socialUser->getName()]
            );

            Auth::login($user, true);
            return Redirect::to($redirectUrl)->with('success', __("Bạn đã đăng nhập bằng {$providerName} thành công!"));
        }

        return Redirect::to($redirectUrl)->withErrors(['message' => __('Email của bạn không hợp lệ.')]);
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
