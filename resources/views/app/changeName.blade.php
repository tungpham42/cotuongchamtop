@extends('layouts.app')


@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-edit"></i> {{ __("Đổi tên") }}
                    @include('layout.partials.app.tourBtn')
                </div>
                <div class="card-body">
                    <!-- Add this block for the success message -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($player->id == auth()->id() && !str_contains(url()->current(), url('/ky-thu').'/'))
                    <div class="col-md-12">
                        <form method="POST" action="{{ localized_url('change.name') }}">
                            @csrf
                            <div class="row mb-3">
                                <label for="current_name" class="col-md-4 col-form-label text-md-end">{{ __("Tên hiện tại") }}</label>
                                <div class="col-md-6">
                                    <input data-step="1" data-intro="{{ __("Tên hiện tại bắt buộc phải nhập để thay đổi tên") }}" type="text" id="current_name" name="current_name" class="form-control" readonly required autofocus value="{{ auth()->user()->name }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="new_name" class="col-md-4 col-form-label text-md-end">{{ __("Tên mới") }}</label>
                                <div class="col-md-6">
                                    <input data-step="2" data-intro="{{ __("Nhập vào tên mới của bạn") }}" type="text" id="new_name" name="new_name" class="form-control @error('new_name') is-invalid @enderror" required>
                                    @error('new_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button data-step="3" data-intro="{{ __("Ấn vào đây để đổi tên mới") }}" type="submit" class="btn btn-danger">
                                        <i class="fad fa-user-edit"></i> {{ __("Đổi tên") }}
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
