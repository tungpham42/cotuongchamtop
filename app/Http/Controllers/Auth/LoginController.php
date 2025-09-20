<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        // Store the previous URL in the session
        Session::put('previousUrl', url()->previous());

        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Perform the login logic here
        // Validate the request data
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to log in the user
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            // Authentication successful
            $user = User::where('email', $request->email)->first();
            Auth::login($user);

            // Get the previous URL from the session
            $previousUrl = Session::get('previousUrl');

            // Redirect the user to the previous URL
            return Redirect::to($previousUrl);
        } else {
            // Authentication failed
            return back()->withErrors([
                'email' => 'Thông tin đăng nhập được cung cấp không khớp với hồ sơ của chúng tôi.',
            ]);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        Session::put('previousUrl', url()->previous());
        // Change the redirect path below to your desired path
        // return redirect('/thi-dau');
        // return redirect('/thi-dau');
        
        $previousUrl = Session::get('previousUrl');

        // Redirect the user to the previous URL
        return Redirect::to($previousUrl)->with('success', 'Bạn đã đăng xuất thành công!');
    }

    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback()
    {
        $facebookUser = Socialite::driver('facebook')->user();

        if (null !== $facebookUser->getEmail()) {
            $user = User::where('email', $facebookUser->getEmail())->first();
            if (!$user) {
                // Create a new user account for the Facebook user
                $user = User::create([
                    'name' => $facebookUser->getName(),
                    'email' => $facebookUser->getEmail(),
                ]);
                return redirect('/tao-mat-khau')->with('current_email', $facebookUser->getEmail());
            }

            Auth::login($user, true);
    
            // Change the redirect path below to your desired path
            // return redirect('/thi-dau');
            return redirect('/thi-dau')->with('success', 'Bạn đã đăng nhập bằng Facebook thành công!');
        } else {
            // Authentication failed
            return redirect('/thi-dau')->withErrors(['message' => 'Email của bạn không hợp lệ.']);
        }
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->user();

        if (null !== $googleUser->getEmail()) {
            $user = User::where('email', $googleUser->getEmail())->first();
            if (!$user) {
                // Create a new user account for the Facebook user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                ]);
                // return redirect('/tao-mat-khau')->with('current_email', $googleUser->getEmail());
            }

            Auth::login($user, true);
    
            // Change the redirect path below to your desired path
            // return redirect('/thi-dau');
            // return redirect('/thi-dau')->with('success', 'Bạn đã đăng nhập bằng Google thành công!');
            $previousUrl = Session::get('previousUrl');

            // Redirect the user to the previous URL
            return Redirect::to($previousUrl)->with('success', 'Bạn đã đăng nhập bằng Google thành công!');
        } else {
            // Authentication failed
            $previousUrl = Session::get('previousUrl');

            // Redirect the user to the previous URL
            return Redirect::to($previousUrl)->withErrors(['message' => 'Email của bạn không hợp lệ.']);
            // return redirect('/thi-dau')->withErrors(['message' => 'Email của bạn không hợp lệ.']);
        }
    }

    public function redirectToGithub()
    {
        return Socialite::driver('github')->redirect();
    }

    public function handleGithubCallback()
    {
        $githubUser = Socialite::driver('github')->user();

        if (null !== $githubUser->getEmail()) {
            $user = User::where('email', $githubUser->getEmail())->first();
            if (!$user) {
                // Create a new user account for the Facebook user
                $user = User::create([
                    'name' => $githubUser->getName(),
                    'email' => $githubUser->getEmail(),
                ]);
                return redirect('/tao-mat-khau')->with('current_email', $githubUser->getEmail());
            }

            Auth::login($user, true);
    
            // Change the redirect path below to your desired path
            // return redirect('/thi-dau');
            // return redirect('/thi-dau')->with('success', 'Bạn đã đăng nhập bằng GitHub thành công!');
            $previousUrl = Session::get('previousUrl');

            // Redirect the user to the previous URL
            return Redirect::to($previousUrl)->with('success', 'Bạn đã đăng nhập bằng GitHub thành công!');
        } else {
            // Authentication failed
            $previousUrl = Session::get('previousUrl');

            // Redirect the user to the previous URL
            return Redirect::to($previousUrl)->withErrors(['message' => 'Email của bạn không hợp lệ.']);
            // return redirect('/thi-dau')->withErrors(['message' => 'Email của bạn không hợp lệ.']);
        }
    }

    public function redirectToLinkedin()
    {
        return Socialite::driver('linkedin')->redirect();
    }

    public function handleLinkedinCallback()
    {
        $linkedinUser = Socialite::driver('linkedin')->user();

        if (null !== $linkedinUser->getEmail()) {
            $user = User::where('email', $linkedinUser->getEmail())->first();
            if (!$user) {
                // Create a new user account for the Facebook user
                $user = User::create([
                    'name' => $linkedinUser->getName(),
                    'email' => $linkedinUser->getEmail(),
                ]);
                return redirect('/tao-mat-khau')->with('current_email', $linkedinUser->getEmail());
            }

            Auth::login($user, true);
    
            // Change the redirect path below to your desired path
            // return redirect('/thi-dau');
            return redirect('/thi-dau')->with('success', 'Bạn đã đăng nhập bằng LinkedIn thành công!');
        } else {
            // Authentication failed
            return redirect('/thi-dau')->withErrors(['message' => 'Email của bạn không hợp lệ.']);
        }
    }

    public function redirectToGitlab()
    {
        return Socialite::driver('gitlab')->redirect();
    }

    public function handleGitlabCallback()
    {
        $gitlabUser = Socialite::driver('gitlab')->user();

        if (null !== $gitlabUser->getEmail()) {
            $user = User::where('email', $gitlabUser->getEmail())->first();
            if (!$user) {
                // Create a new user account for the Facebook user
                $user = User::create([
                    'name' => $gitlabUser->getName(),
                    'email' => $gitlabUser->getEmail(),
                ]);
                return redirect('/tao-mat-khau')->with('current_email', $gitlabUser->getEmail());
            }

            Auth::login($user, true);
    
            // Change the redirect path below to your desired path
            // return redirect('/thi-dau');
            return redirect('/thi-dau')->with('success', 'Bạn đã đăng nhập bằng GitLab thành công!');
        } else {
            // Authentication failed
            return redirect('/thi-dau')->withErrors(['message' => 'Email của bạn không hợp lệ.']);
        }
    }

    public function redirectToBitbucket()
    {
        return Socialite::driver('bitbucket')->redirect();
    }

    public function handleBitbucketCallback()
    {
        $bitbucketUser = Socialite::driver('bitbucket')->user();

        if (null !== $bitbucketUser->getEmail()) {
            $user = User::where('email', $bitbucketUser->getEmail())->first();
            if (!$user) {
                // Create a new user account for the Facebook user
                $user = User::create([
                    'name' => $bitbucketUser->getName(),
                    'email' => $bitbucketUser->getEmail(),
                ]);
                return redirect('/tao-mat-khau')->with('current_email', $bitbucketUser->getEmail());
            }

            Auth::login($user, true);
    
            // Change the redirect path below to your desired path
            // return redirect('/thi-dau')->withErrors(['message' => 'Invalid email or password.']);
            return redirect('/thi-dau')->with('success', 'Bạn đã đăng nhập bằng Bitbucket thành công!');
        } else {
            // Authentication failed
            return redirect('/thi-dau')->withErrors(['message' => 'Email của bạn không hợp lệ.']);
        }
    }

    public function redirectToZalo()
    {
        return Socialite::driver('zalo')->redirect();
    }

    public function handleZaloCallback()
    {
        $zaloUser = Socialite::driver('zalo')->user();

        if (null !== $zaloUser->getId()) {
            $user = User::where('name', $zaloUser->getName())->first();
            if (!$user) {
                // Create a new user account for the Facebook user
                $user = User::create([
                    'name' => $zaloUser->getName(),
                    'email' => md5(time()).'.zalo@cotuong.top',
                ]);
                // return redirect('/tao-mat-khau')->with('current_email', $zaloUser->getEmail());
            }

            Auth::login($user, true);
    
            // Change the redirect path below to your desired path
            // return redirect('/thi-dau');
            // return redirect('/thi-dau')->with('success', 'Bạn đã đăng nhập bằng Google thành công!');
            $previousUrl = Session::get('previousUrl');

            // Redirect the user to the previous URL
            return Redirect::to($previousUrl)->with('success', 'Bạn đã đăng nhập bằng Zalo thành công!');
        } else {
            // Authentication failed
            $previousUrl = Session::get('previousUrl');

            // Redirect the user to the previous URL
            return Redirect::to($previousUrl)->withErrors(['message' => 'Tài khoản của bạn không hợp lệ.']);
            // return redirect('/thi-dau')->withErrors(['message' => 'Email của bạn không hợp lệ.']);
        }
    }
}
