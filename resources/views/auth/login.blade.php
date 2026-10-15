@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-sign-in"></i> {{ __('Login') }}
                    @include('layouts.partials.app.tourBtn')
                </div>

                <div class="card-body">
                    <form method="POST"
                          action="{{ localized_url('login') }}"
                          id="login-form">
                        @csrf

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-right">
                                {{ __('Email Address') }}
                            </label>

                            <div class="col-md-6">
                                <input
                                    data-step="1"
                                    data-intro="{{ __('Điền vào email của bạn') }}"
                                    id="email"
                                    type="email"
                                    class="form-control form-control-lg @error('email') is-invalid @enderror"
                                    name="email"
                                    value="{{ old('email') }}"
                                    required
                                    autocomplete="email"
                                    autofocus
                                >

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-right">
                                {{ __('Password') }}
                            </label>

                            <div class="col-md-6">
                                <input
                                    data-step="2"
                                    data-intro="{{ __('Điền vào mật khẩu') }}"
                                    id="password"
                                    type="password"
                                    class="form-control form-control-lg @error('password') is-invalid @enderror"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                >

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-4">
                                <div
                                    data-step="3"
                                    data-intro="{{ __('Ghi nhớ trạng thái đăng nhập của bạn') }}"
                                    class="form-check"
                                >
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="remember"
                                        id="remember"
                                        {{ old('remember') ? 'checked' : '' }}
                                    >

                                    <label class="form-check-label" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-12">
                                <div class="d-flex flex-wrap justify-content-center align-items-center gap-5 mt-3">

                                    <button
                                        data-step="4"
                                        data-intro="{{ __('Ấn để đăng nhập') }}"
                                        type="submit"
                                        class="btn btn-lg btn-danger mr-2 mb-2"
                                    >
                                        <i class="fad fa-sign-in"></i>
                                        {{ __('Login') }}
                                    </button>

                                    @if (Route::has('password.request'))
                                        <a
                                            class="btn btn-lg btn-dark mb-2"
                                            href="{{ localized_url('password.request') }}"
                                        >
                                            <i class="fad fa-key"></i>
                                            {{ __('Forgot Your Password?') }}
                                        </a>
                                    @endif

                                    <a
                                        href="{{ localized_url('register') }}"
                                        class="btn btn-lg btn-success text-light px-4 shadow-sm ml-2 mb-2"
                                    >
                                        <i class="fad fa-user-plus mr-2"></i>
                                        {{ __('Đăng ký') }}
                                    </a>

                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
