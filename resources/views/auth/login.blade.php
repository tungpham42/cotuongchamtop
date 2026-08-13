@extends('layouts.app')


@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-sign-in"></i> {{ __('Login') }}
                    @include('layout.partials.app.tourBtn')
                </div>

                <div class="card-body">
                    <div class="social-wrapper w-100 d-flex flex-row flex-wrap justify-content-center align-items-center gap-3 mb-4">
                        <a data-step="5" data-intro="{{ __('Đăng nhập ngay lập tức bằng tài khoản Google của bạn') }}" href="{{ url('/auth/google') }}" class="btn btn-google btn-social btn-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                                <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
                                <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
                                <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
                                <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
                            </svg>
                            {{ __('Google') }}
                        </a>

                        <a data-step="6" data-intro="{{ __('Đăng nhập ngay lập tức bằng tài khoản Facebook của bạn') }}" href="{{ url('/auth/facebook') }}" class="btn btn-facebook btn-social btn-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2.04C6.5 2.04 2 6.53 2 12.06C2 17.06 5.66 21.21 10.44 21.96V14.96H7.9V12.06H10.44V9.85C10.44 7.34 11.93 5.96 14.22 5.96C15.31 5.96 16.45 6.15 16.45 6.15V8.62H15.19C13.95 8.62 13.56 9.39 13.56 10.18V12.06H16.34L15.89 14.96H13.56V21.96A10 10 0 0 0 22 12.06C22 6.53 17.5 2.04 12 2.04Z"/>
                            </svg>
                            {{ __('Facebook') }}
                        </a>
                    </div>
                    {{-- <p class="w-100 text-center">
                        <a data-step="7" data-intro="{{ __('Đăng nhập ngay lập tức bằng tài khoản GitHub của bạn') }}" href="{{ url('/auth/github') }}" class="mt-0 btn btn-github btn-lg btn-dark mx-auto d-inline-block"><i class="fab fa-github"></i> {{ __('Đăng nhập bằng GitHub') }}</a>
                        <a data-step="8" data-intro="{{ __('Đăng nhập ngay lập tức bằng tài khoản LinkedIn của bạn') }}" href="{{ url('/auth/linkedin') }}" class="mt-3 mt-lg-0 ml-lg-2 btn btn-linkedin btn-lg btn-info mx-auto d-inline-block"><i class="fab fa-linkedin-in"></i> {{ __('Đăng nhập bằng LinkedIn') }}</a>
                    </p>
                    <p class="w-100 text-center">
                        <a data-step="9" data-intro="{{ __('Đăng nhập ngay lập tức bằng tài khoản GitLab của bạn') }}" href="{{ url('/auth/gitlab') }}" class="mt-0 btn btn-gitlab btn-lg btn-warning mx-auto d-inline-block"><i class="fab fa-gitlab"></i> {{ __('Đăng nhập bằng GitLab') }}</a>
                        <a data-step="10" data-intro="{{ __('Đăng nhập ngay lập tức bằng tài khoản Bitbucket của bạn') }}" href="{{ url('/auth/bitbucket') }}" class="mt-3 mt-lg-0 ml-lg-2 btn btn-bitbucket btn-lg btn-info mx-auto d-inline-block"><i class="fab fa-bitbucket"></i> {{ __('Đăng nhập bằng Bitbucket') }}</a>
                    </p> --}}
                    <form method="POST" action="{{ localized_url('login') }}" id="login-form">
                        @csrf
                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input data-step="1" data-intro="{{ __('Điền vào email của bạn') }}" id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input data-step="2" data-intro="{{ __('Điền vào mật khẩu') }}" id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-4">
                                <div data-step="3" data-intro="{{ __('Ghi nhớ trạng thái đăng nhập của bạn') }}" class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                    <label class="form-check-label" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-12">
                                <div class="d-flex flex-wrap justify-content-center align-items-center gap-5 mt-3">
                                    <button data-step="4" data-intro="{{ __('Ấn để đăng nhập') }}" type="submit" class="btn btn-lg btn-danger mr-2 mb-2">
                                        <i class="fad fa-sign-in"></i> {{ __('Login') }}
                                    </button>

                                    @if (Route::has('password.request'))
                                        <a class="btn btn-lg btn-dark mb-2" href="{{ localized_url('password.request') }}">
                                            <i class="fad fa-key"></i> {{ __('Forgot Your Password?') }}
                                        </a>
                                    @endif

                                    <a href="{{ localized_url('register') }}" class="btn btn-lg btn-success text-light px-4 shadow-sm ml-2 mb-2">
                                        <i class="fad fa-user-plus mr-2"></i> {{ __('Đăng ký') }}
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
