@extends('layout.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    @if (url()->current() == url('/tao-mat-khau'))
                    <i class="fas fa-lock-alt"></i> Tạo mật khẩu
                    @else
                    <i class="fas fa-key"></i> {{ __('Reset Password') }}
                    @endif
                    @include('layout.partials.app.tourBtn')
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                @if (url()->current() == url('/tao-mat-khau'))
                                <input data-step="1" data-intro="Đây là email hiện tại của bạn" id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" value="{{ session('current_email') }}" required readonly autocomplete="email" autofocus>
                                @else
                                <input data-step="1" data-intro="Điền vào email của bạn" id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                @endif
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button data-step="2" data-intro="Gửi đường dẫn phục hồi mật khẩu đến email của bạn" type="submit" class="btn btn-lg btn-danger">
                                    <i class="fad fa-paper-plane"></i> {{ __('Send Password Reset Link') }}
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
