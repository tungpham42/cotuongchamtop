<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
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

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'points' => 0,
        ]);
    }
}
