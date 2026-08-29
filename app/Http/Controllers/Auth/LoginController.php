<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\Session as DbSession;
use App\Events\PlayersUpdated;
use App\Actions\User\AwardLoginKarmaAction;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function __construct(private AwardLoginKarmaAction $awardLoginKarma)
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
        if (url()->previous() && !str_contains(url()->previous(), localized_url('login')) && !str_contains(url()->previous(), localized_url('register'))) {
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
            $this->awardLoginKarma->execute(Auth::user());

            // Trigger real-time UI update when a user logs in
            broadcast(new PlayersUpdated());

            return Redirect::to($this->redirectTo());
        }

        return back()->withErrors([
            'email' => __('Thông tin đăng nhập được cung cấp không khớp với hồ sơ của chúng tôi.'),
        ]);
    }

    public function logout(Request $request)
    {
        // Capture the user ID and previous URL BEFORE the session is touched
        $userId = Auth::id();

        $locale = app()->getLocale();
        $localizedHome = ($locale === 'vi') ? '/' : '/' . $locale;

        $candidateUrl = url()->previous();

        // Only trust the previous URL if it points back to our own app
        // (guards against open-redirect via a spoofed Referer header)
        $previousUrl = ($candidateUrl && str_starts_with($candidateUrl, url('/')) && $candidateUrl !== localized_url('logout'))
            ? $candidateUrl
            : $localizedHome;

        if ($userId) {
            // Instantly delete all lingering database sessions for this user across all devices/tabs
            DbSession::where('user_id', $userId)->delete();
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

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

        if (null === $socialUser->getEmail()) {
            return Redirect::to($redirectUrl)->withErrors([
                'message' => __('Email của bạn không hợp lệ.'),
            ]);
        }

        $user = User::firstOrCreate(
            ['email' => $socialUser->getEmail()],
            ['name' => $socialUser->getName()]
        );

        return $this->completeSocialLogin($user, $redirectUrl, $providerName);
    }

    /**
     * Shared success path for any social login: auth, karma, broadcast, redirect.
     */
    private function completeSocialLogin(
        User $user,
        string $redirectUrl,
        string $providerName
    ) {
        Auth::login($user, true);

        $karmaReward = $this->awardLoginKarma->execute($user);

        // broadcast(new PlayersUpdated());

        return Redirect::to($redirectUrl)
            ->with('success', __("Bạn đã đăng nhập bằng {$providerName} thành công!"))
            ->with('karma_earned', $karmaReward);
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
