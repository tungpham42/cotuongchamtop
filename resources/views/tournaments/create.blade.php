@extends('layout.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-secondary text-dark border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-dark text-warning border-0" style="border-radius: 12px 12px 0 0;">
                    <h4 class="mb-0"><i class="fad fa-plus-circle"></i> {{ __('Tạo Giải Đấu Mới') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('tournaments.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group text-dark">
                            <label>{{ __('Tên Giải Đấu') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control bg-dark text-dark border-secondary" required>
                        </div>

                        <div class="form-group text-dark">
                            <label>{{ __('Hình Nền (Cover Photo)') }}</label>
                            <input type="file" name="cover_photo" class="form-control-file bg-light text-dark" accept="image/*">
                        </div>

                        <div class="form-group text-dark">
                            <label>{{ __('Mô tả') }}</label>
                            <textarea name="description" class="form-control bg-light text-dark border-secondary" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group text-dark">
                                <label>{{ __('Ngày Bắt Đầu') }} <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="start_date" class="form-control bg-light text-dark border-secondary" required>
                            </div>

                            <div class="col-md-4 form-group text-dark">
                                <label>{{ __('Số lượng kỳ thủ') }} <span class="text-danger">*</span></label>
                                <input type="number" name="max_players" class="form-control bg-light text-dark border-secondary" min="2" max="16" value="16" required>
                            </div>

                            <div class="col-md-4 form-group text-dark">
                                <label>{{ __('Trạng thái') }} <span class="text-danger">*</span></label>
                                <select name="status" class="form-control bg-light text-dark border-secondary" required>
                                    <option value="open">{{ __('Mở đăng ký') }}</option>
                                    <option value="in_progress">{{ __('Đang diễn ra') }}</option>
                                    <option value="completed">{{ __('Đã kết thúc') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 text-right">
                            <a href="{{ route('tournaments.index') }}" class="btn text-dark btn-outline-light mr-2">{{ __('Hủy') }}</a>
                            <button type="submit" class="btn btn-warning text-dark font-weight-bold"><i class="fad fa-save"></i> {{ __('Lưu Giải Đấu') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
