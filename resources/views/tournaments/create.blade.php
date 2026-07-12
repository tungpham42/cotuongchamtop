@extends('layout.app')


@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg" style="border-radius: 4px; background: rgba(28, 17, 10, 0.85); border: 2px solid var(--royal-gold); box-shadow: 0 0 20px rgba(0, 0, 0, 0.8), inset 0 0 15px rgba(212, 175, 55, 0.1);">
                <div class="card-header border-0 py-3" style="background: linear-gradient(to bottom, #8a1515, #5c0a0a); border-bottom: 2px solid var(--royal-gold) !important; border-radius: 2px 2px 0 0;">
                    <h4 class="mb-0" style="color: var(--royal-gold); font-family: 'Texturina', serif; text-transform: uppercase; letter-spacing: 1px;"><i class="fad fa-plus-circle"></i> {{ __('Tạo Giải Đấu Mới') }}</h4>
                </div>
                <div class="card-body p-4">
                    <form action="{{ localized_url('tournaments.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label style="color: var(--royal-gold); font-weight: bold;">{{ __('Tên Giải Đấu') }} <span style="color: var(--royal-red-light);">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label style="color: var(--royal-gold); font-weight: bold;">{{ __('Hình Nền (Cover Photo)') }}</label>
                            <input type="file" name="cover_photo" class="form-control-file" style="color: var(--royal-gold-light);" accept="image/*">
                        </div>

                        <div class="form-group">
                            <label style="color: var(--royal-gold); font-weight: bold;">{{ __('Mô tả') }}</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label style="color: var(--royal-gold); font-weight: bold;">{{ __('Ngày Bắt Đầu') }} <span style="color: var(--royal-red-light);">*</span></label>
                                <input type="datetime-local" name="start_date" class="form-control" required>
                            </div>

                            <div class="col-md-4 form-group">
                                <label style="color: var(--royal-gold); font-weight: bold;">{{ __('Số lượng kỳ thủ') }} <span style="color: var(--royal-red-light);">*</span></label>
                                <input type="number" name="max_players" class="form-control" min="2" max="16" value="16" required>
                            </div>

                            <div class="col-md-4 form-group">
                                <label style="color: var(--royal-gold); font-weight: bold;">{{ __('Trạng thái') }} <span style="color: var(--royal-red-light);">*</span></label>
                                <select name="status" class="form-control" required>
                                    <option value="open">{{ __('Mở đăng ký') }}</option>
                                    <option value="in_progress">{{ __('Đang diễn ra') }}</option>
                                    <option value="completed">{{ __('Đã kết thúc') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 text-right">
                            <a href="{{ localized_url('tournaments.index') }}" class="btn mr-2" style="color: var(--royal-gold-light); border: 1px solid var(--royal-wood);">{{ __('Hủy') }}</a>
                            <button type="submit" class="btn font-weight-bold pulse-gold" style="background: linear-gradient(to bottom, #d4af37, #b89020); color: var(--royal-red); border: 2px solid #fff;"><i class="fad fa-save"></i> {{ __('Lưu Giải Đấu') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
