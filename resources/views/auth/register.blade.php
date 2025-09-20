@extends('layout.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-plus"></i> {{ __('Register') }}
                    @include('layout.partials.app.tourBtn')
                </div>

                <div class="card-body">
                    <p class="w-100 text-center">
                        <a data-step="6" data-intro="Đăng ký ngay lập tức bằng tài khoản Google của bạn" href="{{ url('/auth/google') }}" class="mt-0 btn btn-google btn-lg btn-danger mx-auto d-inline-block"><i class="fab fa-google"></i> Đăng ký bằng Google</a>
                        <a data-step="7" data-intro="Đăng ký ngay lập tức bằng tài khoản Zalo của bạn" href="{{ url('/auth/zalo') }}" class="mt-3 mt-lg-0 ml-lg-2 btn btn-zalo btn-lg btn-info mx-auto d-inline-block"><i class="fad fa-phone-square-alt"></i> Đăng ký bằng Zalo</a>
                        {{-- <a data-step="7" data-intro="Đăng ký ngay lập tức bằng tài khoản GitHub của bạn" href="{{ url('/auth/github') }}" class="mt-3 mt-lg-0 ml-lg-2 btn btn-github btn-lg btn-dark mx-auto d-inline-block"><i class="fab fa-github"></i> Đăng ký bằng GitHub</a> --}}
                        {{-- <a data-step="6" data-intro="Đăng ký ngay lập tức bằng tài khoản Facebook của bạn" href="{{ url('/auth/facebook') }}" class="mt-0 btn btn-facebook btn-lg btn-info mx-auto d-inline-block"><i class="fab fa-facebook-f"></i> Đăng ký bằng Facebook</a>
                        <a data-step="7" data-intro="Đăng ký ngay lập tức bằng tài khoản Google của bạn" href="{{ url('/auth/google') }}" class="mt-3 mt-lg-0 ml-lg-2 btn btn-google btn-lg btn-danger mx-auto d-inline-block"><i class="fab fa-google"></i> Đăng ký bằng Google</a> --}}
                    </p>
                    {{-- <p class="w-100 text-center">
                        <a data-step="8" data-intro="Đăng ký ngay lập tức bằng tài khoản Github của bạn" href="{{ url('/auth/github') }}" class="mt-0 btn btn-github btn-lg btn-dark mx-auto d-inline-block"><i class="fab fa-github"></i> Đăng ký bằng GitHub</a>
                        <a data-step="9" data-intro="Đăng ký ngay lập tức bằng tài khoản LinkedIn của bạn" href="{{ url('/auth/linkedin') }}" class="mt-3 mt-lg-0 ml-lg-2 btn btn-linkedin btn-lg btn-info mx-auto d-inline-block"><i class="fab fa-linkedin-in"></i> Đăng ký bằng LinkedIn</a>
                    </p>
                    <p class="w-100 text-center">
                        <a data-step="10" data-intro="Đăng ký ngay lập tức bằng tài khoản GitLab của bạn" href="{{ url('/auth/gitlab') }}" class="mt-0 btn btn-gitlab btn-lg btn-warning mx-auto d-inline-block"><i class="fab fa-gitlab"></i> Đăng ký bằng GitLab</a>
                        <a data-step="11" data-intro="Đăng ký ngay lập tức bằng tài khoản Bitbucket của bạn" href="{{ url('/auth/bitbucket') }}" class="mt-3 mt-lg-0 ml-lg-2 btn btn-bitbucket btn-lg btn-info mx-auto d-inline-block"><i class="fab fa-bitbucket"></i> Đăng ký bằng Bitbucket</a>
                    </p> --}}
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Name') }}</label>

                            <div class="col-md-6">
                                <input data-step="1" data-intro="Điền vào tên của bạn" id="name" type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input data-step="2" data-intro="Điền vào email của bạn" id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

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
                                <input data-step="3" data-intro="Điền vào mật khẩu của bạn. Mật khẩu mới phải ít nhất 8 ký tự" id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input data-step="4" data-intro="Xác nhận mật khẩu của bạn" id="password-confirm" type="password" class="form-control form-control-lg" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button data-step="5" data-intro="Ấn vào đây để tạo tài khoản mới" type="submit" class="btn btn-lg btn-danger">
                                    <i class="fad fa-user-plus"></i> {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
