@extends('layout.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-secondary text-light border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-dark text-warning border-0" style="border-radius: 12px 12px 0 0;">
                    <h4 class="mb-0"><i class="fad fa-edit"></i> {{ __('Sửa Giải Đấu') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('tournaments.update', $tournament->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group text-dark">
                            <label>{{ __('Tên Giải Đấu') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control bg-dark text-light border-secondary" value="{{ $tournament->name }}" required>
                        </div>

                        <div class="form-group text-dark">
                            <label>{{ __('Mô tả') }}</label>
                            <textarea name="description" class="form-control bg-dark text-light border-secondary" rows="3">{{ $tournament->description }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group text-dark">
                                <label>{{ __('Ngày Bắt Đầu') }} <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="start_date" class="form-control bg-dark text-light border-secondary" value="{{ date('Y-m-d\TH:i', strtotime($tournament->start_date)) }}" required>
                            </div>

                            <div class="col-md-4 form-group text-dark">
                                <label>{{ __('Số lượng kỳ thủ') }} <span class="text-danger">*</span></label>
                                <input type="number" name="max_players" class="form-control bg-dark text-light border-secondary" min="2" value="{{ $tournament->max_players }}" required>
                            </div>

                            <div class="col-md-4 form-group text-dark">
                                <label>{{ __('Trạng thái') }} <span class="text-danger">*</span></label>
                                <select name="status" class="form-control bg-dark text-light border-secondary" required>
                                    <option value="open" {{ $tournament->status == 'open' ? 'selected' : '' }}>{{ __('Mở đăng ký') }}</option>
                                    <option value="in_progress" {{ $tournament->status == 'in_progress' ? 'selected' : '' }}>{{ __('Đang diễn ra') }}</option>
                                    <option value="completed" {{ $tournament->status == 'completed' ? 'selected' : '' }}>{{ __('Đã kết thúc') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 text-right">
                            <a href="{{ route('tournaments.show', $tournament->id) }}" class="btn btn-outline-light text-dark mr-2">{{ __('Hủy') }}</a>
                            <button type="submit" class="btn btn-warning text-dark font-weight-bold"><i class="fad fa-save"></i> {{ __('Cập nhật') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
