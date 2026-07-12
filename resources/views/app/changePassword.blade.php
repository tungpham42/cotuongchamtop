@extends('layout.app')
@inject('userPresenter', 'App\Presenters\UserPresenter')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-lock-alt"></i> {{ __("Đổi mật khẩu") }}
                    @include('layout.partials.app.tourBtn')
                </div>
                <div class="card-body">
                    @if ($player->id == auth()->id() && !str_contains(url()->current(), url('/ky-thu').'/'))
                    <div class="col-md-12">
                        <form method="POST" action="{{ localized_url('change.password') }}">
                            @csrf
                            <input type="hidden" value="{{ auth()->user()->id }}" name="current_id">
                            <div class="row mb-3">
                                <label for="current_password" class="col-md-4 col-form-label text-md-end">{{ __("Mật khẩu hiện tại") }}</label>
                                <div class="col-md-6">
                                    <input data-step="1" data-intro="{{ __("Mật khẩu hiện tại bắt buộc phải nhập đúng") }}" type="password" id="current_password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required autofocus>
                                    @error('current_password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="new_password" class="col-md-4 col-form-label text-md-end">{{ __("Mật khẩu mới") }}</label>
                                <div class="col-md-6">
                                    <input data-step="2" data-intro="{{ __("Mật khẩu mới phải ít nhất 8 ký tự") }}" type="password" id="new_password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" required>
                                    @error('new_password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="new_confirm_password" class="col-md-4 col-form-label text-md-end">{{ __("Lặp lại mật khẩu") }}</label>
                                <div class="col-md-6">
                                    <input data-step="3" data-intro="{{ __("Mật khẩu lặp lại và mật khẩu mới phải giống nhau") }}" type="password" id="new_confirm_password" name="new_confirm_password" class="form-control @error('new_confirm_password') is-invalid @enderror">
                                    @error('new_confirm_password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button data-step="4" data-intro="{{ __("Ấn vào đây để đổi mật khẩu mới") }}" type="submit" class="btn btn-danger">
                                        <i class="fad fa-lock-alt"></i> {{ __("Đổi mật khẩu") }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
