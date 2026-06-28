@guest
<div class="row mb-4 justify-content-center">
    <div class="col-lg-8 col-md-10 col-12 text-center">
        <div class="card shadow-lg" style="border-radius: 8px; background: rgba(28, 17, 10, 0.85); border: 2px solid var(--royal-gold); box-shadow: 0 0 20px rgba(0, 0, 0, 0.8), inset 0 0 15px rgba(212, 175, 55, 0.1); overflow: hidden;">
            {{-- Header Banner of the Card --}}
            <div class="card-header border-0 py-2 d-flex align-items-center justify-content-center" style="background: linear-gradient(to bottom, #8a1515, #5c0a0a); border-bottom: 2px solid var(--royal-gold) !important; color: var(--royal-gold);">
                <i class="fad fa-swords fa-lg mr-2" style="color: var(--royal-gold);"></i>
                <strong style="letter-spacing: 1px; font-size: 1.1rem; text-transform: uppercase; font-family: 'Texturina', serif;">{{ __('GIẢI ĐẤU CỜ TƯỚNG ĐANG CHỜ BẠN!') }}</strong>
            </div>

            <div class="card-body p-4">
                <p class="lead mb-3" style="font-size: 1.05rem; color: var(--royal-gold-light);">
                    {{ __('Bạn đang chơi với tư cách Khách. Bạn có biết tài khoản miễn phí cho phép bạn tham gia các giải đấu, theo dõi Elo và ghi danh vào Bảng Xếp Hạng?') }}
                </p>

                <div class="d-flex flex-column flex-md-row justify-content-center gap-3 mt-3">
                    <a href="{{ localized_url('tournaments.index') }}" class="btn font-weight-bold px-4 py-2 mx-md-2 mb-2 mb-md-0" style="color: var(--royal-gold); border: 1px solid var(--royal-gold); border-radius: 4px; transition: 0.3s; background: transparent;">
                    <i class="fad fa-eye"></i> {{ __('Xem Các Giải Đấu') }}
                    </a>
                    <a href="{{ localized_url('register') }}" class="btn font-weight-bold px-4 py-2 mx-md-2 pulse-gold" style="background: linear-gradient(to bottom, #d4af37, #b89020); color: var(--royal-red); border: 1px solid #fff; border-radius: 4px; box-shadow: 2px 2px 5px rgba(0,0,0,0.5);">
                    <i class="fad fa-user-plus"></i> {{ __('Đăng Ký Miễn Phí Ngay') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="row mb-4 justify-content-center">
    <div class="col-lg-8 col-md-10 col-12 text-center">
        <div class="card shadow-lg" style="border-radius: 8px; background: rgba(28, 17, 10, 0.85); border: 2px solid var(--royal-gold); box-shadow: 0 0 20px rgba(0, 0, 0, 0.8), inset 0 0 15px rgba(212, 175, 55, 0.1); overflow: hidden;">
            {{-- Header Banner of the Card (Logged In) --}}
            <div class="card-header border-0 py-2 d-flex align-items-center justify-content-center" style="background: linear-gradient(to bottom, #8a1515, #5c0a0a); border-bottom: 2px solid var(--royal-gold) !important; color: var(--royal-gold);">
                <i class="fad fa-swords fa-lg mr-2" style="color: var(--royal-gold);"></i>
                <strong style="letter-spacing: 1px; font-size: 1.1rem; text-transform: uppercase; font-family: 'Texturina', serif;">{{ __('CHINH PHỤC BẢNG XẾP HẠNG!') }}</strong>
            </div>

            <div class="card-body p-4">
                <p class="lead mb-3" style="font-size: 1.05rem; color: var(--royal-gold-light);">
                    {{ __('Chào mừng kỳ thủ') }} <strong style="color: var(--royal-gold);">{{ auth()->user()->name }}</strong> {{ __('đã quay trở lại! Hãy tiếp tục rèn luyện, tham gia các giải đấu và vươn lên đỉnh cao.') }}
                </p>

                <div class="d-flex flex-column flex-md-row justify-content-center gap-3 mt-3">
                    <a href="{{ localized_url('tournaments.index') }}" class="btn font-weight-bold px-4 py-2 mx-md-2 mb-2 mb-md-0" style="color: var(--royal-gold); border: 1px solid var(--royal-gold); border-radius: 4px; transition: 0.3s; background: transparent;">
                        <i class="fad fa-trophy"></i> {{ __('Giải Đấu Đang Diễn Ra') }}
                    </a>
                    <a href="{{ localized_url('app.ranking') }}" class="btn btn-danger font-weight-bold px-4 py-2 mx-md-2 pulse-red" style="border-radius: 4px;">
                        <i class="fad fa-chart-line"></i> {{ __('Xem Bảng Xếp Hạng') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endguest
