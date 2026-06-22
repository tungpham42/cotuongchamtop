<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Events\PlayersUpdated;
use Illuminate\Http\Request;
use Maicol07\SSO\Flarum;

class RegisterController extends Controller
{
    use RegistersUsers;

    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Dynamically determines the redirect path after registration.
     */
    public function redirectTo()
    {
        $locale = app()->getLocale();
        $localizedHome = ($locale === 'vi') ? '/' : '/' . $locale;

        return Session::get('previousUrl', $localizedHome);
    }

    /**
     * Override the trait method to inject hreflang data into the view.
     */
    public function showRegistrationForm()
    {
        return view('auth.register', localized_page_data('register', app()->getLocale()));
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'min:3', 'max:15', 'unique:users,name'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ],
        [
            'name.required' => __('Tên bắt buộc điền.'),
            'name.string' => __('Định dạng tên sai.'),
            'name.min' => __('Tên phải ít nhất 3 ký tự.'),
            'name.max' => __('Tên phải ít hơn 16 ký tự.'),
            'name.unique' => __('Tên này đã được sử dụng.'),
            'email.required' => __('Email bắt buộc điền.'),
            'email.string' => __('Định dạng email sai.'),
            'email.email' => __('Cấu trúc email sai.'),
            'email.unique' => __('Email này đã được sử dụng.'),
            'password.required' => __('Mật khẩu bắt buộc điền.'),
            'password.string' => __('Định dạng mật khẩu sai.'),
            'password.min' => __('Mật khẩu mới phải ít nhất 8 ký tự.'),
            'password.confirmed' => __('Mật khẩu phải trùng nhau.'),
        ]);
    }

    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'points' => 0,
        ]);

        // Trigger real-time UI update when a new user registers
        // broadcast(new PlayersUpdated());

        return $user;
    }

    /**
     * Hàm này được Laravel tự động gọi ngay sau khi create() thành công
     * và user đã được Auth::login().
     */
    protected function registered(Request $request, $user)
    {
        // --- BẮT ĐẦU: ĐỒNG BỘ REGISTER SANG FLARUM ---
        try {
            $flarum = new Flarum([
                'url' => env('FLARUM_URL'),
                'root_domain' => env('FLARUM_ROOT_DOMAIN'),
                'api_key' => env('FLARUM_API_KEY'),
                'remember' => true,
            ]);

            // Hàm login() của Flarum SSO có tính năng tự động tạo user bên Flarum nếu chưa tồn tại
            $flarum->login($user->name, $user->email);
        } catch (\Exception $e) {
            Log::error('Flarum Register/Login Error: ' . $e->getMessage());
        }
        // --- KẾT THÚC ---
    }
}
