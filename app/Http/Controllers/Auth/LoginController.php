<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Helper method to get the correct previous URL or localized home fallback.
     */
    private function getRedirectUrl()
    {
        $locale = app()->getLocale();
        $localizedHome = ($locale === 'vi') ? '/' : '/' . $locale;

        return Session::get('previousUrl', $localizedHome);
    }

    public function showLoginForm()
    {
        // Only update the previous URL if the user didn't just come from a failed login attempt
        if (!str_contains(url()->previous(), 'login')) {
            Session::put('previousUrl', url()->previous());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = User::where('email', $request->email)->first();
            Auth::login($user);

            return Redirect::to($this->getRedirectUrl());
        } else {
            return back()->withErrors([
                'email' => __('Thông tin đăng nhập được cung cấp không khớp với hồ sơ của chúng tôi.'),
            ]);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $previousUrl = $this->getRedirectUrl();

        // Update the previous URL for the next potential action after logout
        Session::put('previousUrl', url()->previous());

        return Redirect::to($previousUrl)->with('success', __('Bạn đã đăng xuất thành công!'));
    }

    /*
    |--------------------------------------------------------------------------
    | Facebook
    |--------------------------------------------------------------------------
    */
    public function redirectToFacebook()
    {
        if (!str_contains(url()->previous(), 'login')) {
            Session::put('previousUrl', url()->previous());
        }
        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback()
    {
        $facebookUser = Socialite::driver('facebook')->user();
        $redirectUrl = $this->getRedirectUrl();

        if (null !== $facebookUser->getEmail()) {
            $user = User::firstOrCreate(
                ['email' => $facebookUser->getEmail()],
                ['name' => $facebookUser->getName()]
            );

            Auth::login($user, true);
            return Redirect::to($redirectUrl)->with('success', __('Bạn đã đăng nhập bằng Facebook thành công!'));
        }

        return Redirect::to($redirectUrl)->withErrors(['message' => __('Email của bạn không hợp lệ.')]);
    }

    /*
    |--------------------------------------------------------------------------
    | Google
    |--------------------------------------------------------------------------
    */
    public function redirectToGoogle()
    {
        if (!str_contains(url()->previous(), 'login')) {
            Session::put('previousUrl', url()->previous());
        }
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->user();
        $redirectUrl = $this->getRedirectUrl();

        if (null !== $googleUser->getEmail()) {
            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                ['name' => $googleUser->getName()]
            );

            Auth::login($user, true);
            return Redirect::to($redirectUrl)->with('success', __('Bạn đã đăng nhập bằng Google thành công!'));
        }

        return Redirect::to($redirectUrl)->withErrors(['message' => __('Email của bạn không hợp lệ.')]);
    }

    /*
    |--------------------------------------------------------------------------
    | GitHub
    |--------------------------------------------------------------------------
    */
    public function redirectToGithub()
    {
        if (!str_contains(url()->previous(), 'login')) {
            Session::put('previousUrl', url()->previous());
        }
        return Socialite::driver('github')->redirect();
    }

    public function handleGithubCallback()
    {
        $githubUser = Socialite::driver('github')->user();
        $redirectUrl = $this->getRedirectUrl();

        if (null !== $githubUser->getEmail()) {
            $user = User::firstOrCreate(
                ['email' => $githubUser->getEmail()],
                ['name' => $githubUser->getName()]
            );

            Auth::login($user, true);
            return Redirect::to($redirectUrl)->with('success', __('Bạn đã đăng nhập bằng GitHub thành công!'));
        }

        return Redirect::to($redirectUrl)->withErrors(['message' => __('Email của bạn không hợp lệ.')]);
    }

    /*
    |--------------------------------------------------------------------------
    | LinkedIn
    |--------------------------------------------------------------------------
    */
    public function redirectToLinkedin()
    {
        if (!str_contains(url()->previous(), 'login')) {
            Session::put('previousUrl', url()->previous());
        }
        return Socialite::driver('linkedin')->redirect();
    }

    public function handleLinkedinCallback()
    {
        $linkedinUser = Socialite::driver('linkedin')->user();
        $redirectUrl = $this->getRedirectUrl();

        if (null !== $linkedinUser->getEmail()) {
            $user = User::firstOrCreate(
                ['email' => $linkedinUser->getEmail()],
                ['name' => $linkedinUser->getName()]
            );

            Auth::login($user, true);
            return Redirect::to($redirectUrl)->with('success', __('Bạn đã đăng nhập bằng LinkedIn thành công!'));
        }

        return Redirect::to($redirectUrl)->withErrors(['message' => __('Email của bạn không hợp lệ.')]);
    }

    /*
    |--------------------------------------------------------------------------
    | GitLab
    |--------------------------------------------------------------------------
    */
    public function redirectToGitlab()
    {
        if (!str_contains(url()->previous(), 'login')) {
            Session::put('previousUrl', url()->previous());
        }
        return Socialite::driver('gitlab')->redirect();
    }

    public function handleGitlabCallback()
    {
        $gitlabUser = Socialite::driver('gitlab')->user();
        $redirectUrl = $this->getRedirectUrl();

        if (null !== $gitlabUser->getEmail()) {
            $user = User::firstOrCreate(
                ['email' => $gitlabUser->getEmail()],
                ['name' => $gitlabUser->getName()]
            );

            Auth::login($user, true);
            return Redirect::to($redirectUrl)->with('success', __('Bạn đã đăng nhập bằng GitLab thành công!'));
        }

        return Redirect::to($redirectUrl)->withErrors(['message' => __('Email của bạn không hợp lệ.')]);
    }

    /*
    |--------------------------------------------------------------------------
    | Bitbucket
    |--------------------------------------------------------------------------
    */
    public function redirectToBitbucket()
    {
        if (!str_contains(url()->previous(), 'login')) {
            Session::put('previousUrl', url()->previous());
        }
        return Socialite::driver('bitbucket')->redirect();
    }

    public function handleBitbucketCallback()
    {
        $bitbucketUser = Socialite::driver('bitbucket')->user();
        $redirectUrl = $this->getRedirectUrl();

        if (null !== $bitbucketUser->getEmail()) {
            $user = User::firstOrCreate(
                ['email' => $bitbucketUser->getEmail()],
                ['name' => $bitbucketUser->getName()]
            );

            Auth::login($user, true);
            return Redirect::to($redirectUrl)->with('success', __('Bạn đã đăng nhập bằng Bitbucket thành công!'));
        }

        return Redirect::to($redirectUrl)->withErrors(['message' => __('Email của bạn không hợp lệ.')]);
    }

    /*
    |--------------------------------------------------------------------------
    | Zalo
    |--------------------------------------------------------------------------
    */
    public function redirectToZalo()
    {
        if (!str_contains(url()->previous(), 'login')) {
            Session::put('previousUrl', url()->previous());
        }
        return Socialite::driver('zalo')->redirect();
    }

    public function handleZaloCallback()
    {
        $zaloUser = Socialite::driver('zalo')->user();
        $redirectUrl = $this->getRedirectUrl();

        if (null !== $zaloUser->getId()) {
            $user = User::firstOrCreate(
                ['name' => $zaloUser->getName()],
                ['email' => md5(time()).'.zalo@cotuong.top']
            );

            Auth::login($user, true);
            return Redirect::to($redirectUrl)->with('success', __('Bạn đã đăng nhập bằng Zalo thành công!'));
        }

        return Redirect::to($redirectUrl)->withErrors(['message' => __('Tài khoản của bạn không hợp lệ.')]);
    }
}
