@extends('layouts.app')

@section('hasPageH1', '1')

@section('og_image', asset('img/xiangqi_vip.jpg'))

@section('content')
<div class="container py-4">
    {{-- Hero Section --}}
    <div class="text-center text-white mb-5">
        <span class="badge badge-warning text-dark px-3 py-2 text-uppercase font-weight-bold mb-3" style="letter-spacing: 2px;">
            <i class="fas fa-crown"></i> {{ __('Kỳ Thủ VIP') }}
        </span>
        <h1 class="display-4 font-weight-bold text-gold" style="text-shadow: 0 2px 10px rgba(212,175,55,0.4);">
            {{ __('Nâng cấp VIP - Kỳ Thủ Hoàng Gia') }}
        </h1>
        <p class="lead text-light mx-auto" style="max-width: 720px; font-size: 1.15rem;">
            {{ __('Mở khóa Huấn luyện viên AI Pikafish phân tích chuyên sâu từng nước đi, không giới hạn gợi ý thế cờ, trải nghiệm mượt mà 100% không quảng cáo và khẳng định đẳng cấp với huy hiệu Vương Miện Hoàng Gia.') }}
        </p>

        @auth
            @if(auth()->user()->isStandard())
                <div class="alert alert-success d-inline-block px-4 py-2 mt-2 shadow">
                    <i class="fas fa-check-circle"></i> {{ __('Gói hiện tại của bạn') }}: <strong>{{ auth()->user()->getVipLabel() }}</strong>
                    @if(auth()->user()->subscription_ends_at)
                        <span class="small ml-1">({{ __('hết hạn') }} {{ auth()->user()->subscription_ends_at->format('d/m/Y') }})</span>
                    @else
                        <span class="badge badge-warning ml-1 text-dark">{{ __('Vĩnh viễn') }}</span>
                    @endif
                </div>
            @endif
        @endauth
    </div>

    {{-- Pricing Cards --}}
    <div class="row justify-content-center align-items-stretch mb-5">
        @php
            $plansList = config('payos.plans', [
                'vip_week' => ['name' => 'Kỳ Thủ VIP (1 Tuần)', 'amount' => 19000, 'badge' => 'Trải nghiệm', 'popular' => false],
                'vip_month' => ['name' => 'Kỳ Thủ VIP (1 Tháng)', 'amount' => 49000, 'badge' => 'Phổ biến nhất', 'popular' => true],
                'standard' => ['name' => 'Kỳ Thủ Hoàng Gia (Vĩnh Viễn)', 'amount' => 199000, 'badge' => 'Tiết kiệm nhất', 'popular' => false],
            ]);
        @endphp

        @foreach($plansList as $key => $plan)
            @php $isPopular = !empty($plan['popular']); @endphp
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-lg text-white border-{{ $isPopular ? 'warning' : 'secondary' }}" style="background: {{ $isPopular ? 'linear-gradient(180deg, #1f1f2e 0%, #151522 100%)' : '#1a1a24' }}; border-width: {{ $isPopular ? '2px' : '1px' }}; border-radius: 16px; position: relative; overflow: hidden;">
                    @if(!empty($plan['badge']))
                        <div class="position-absolute" style="top: 15px; right: 15px;">
                            <span class="badge badge-{{ $isPopular ? 'warning text-dark' : 'danger' }} px-3 py-1 font-weight-bold">
                                {{ __($plan['badge']) }}
                            </span>
                        </div>
                    @endif

                    <div class="card-body d-flex flex-column p-4 text-center">
                        <div class="my-3">
                            <i class="fas {{ $key === 'standard' ? 'fa-gem' : ($isPopular ? 'fa-crown' : 'fa-chess-knight') }} fa-3x {{ $isPopular ? 'text-warning' : 'text-danger' }}"></i>
                        </div>
                        <h4 class="card-title font-weight-bold mb-3 text-white">
                            {{ __($plan['name']) }}
                        </h4>

                        <div class="mb-4">
                            <span class="display-4 font-weight-bold text-gold">{{ number_format($plan['amount'], 0, ',', '.') }}</span>
                            <span class="h5 text-muted">đ</span>
                            <div class="small text-muted mt-1">
                                @if($key === 'vip_week')
                                    {{ __('thanh toán 1 lần / 7 ngày') }}
                                @elseif($key === 'vip_month')
                                    {{ __('thanh toán 1 lần / 30 ngày') }}
                                @else
                                    {{ __('thanh toán 1 lần duy nhất, dùng mãi mãi') }}
                                @endif
                            </div>
                        </div>

                        <ul class="list-unstyled text-left mb-4 flex-grow-1" style="font-size: 0.95rem; line-height: 2;">
                            <li><i class="fas fa-check-circle text-success mr-2"></i> {{ __('Phân tích nước đi bằng AI Engine Pikafish không giới hạn') }}</li>
                            <li><i class="fas fa-check-circle text-success mr-2"></i> {{ __('Trợ lý & Huấn luyện viên AI phân tích thế cờ') }}</li>
                            <li><i class="fas fa-check-circle text-success mr-2"></i> {{ __('Ẩn 100% quảng cáo') }}</li>
                            <li><i class="fas fa-check-circle text-success mr-2"></i> {{ __('Huy hiệu vương miện VIP danh giá') }}</li>
                            <li><i class="fas fa-check-circle text-success mr-2"></i> {{ __('Ưu tiên kết nối máy chủ tốc độ cao') }}</li>
                        </ul>

                        @auth
                            @php
                                $user = auth()->user();
                                $userPlan = $user->subscription_plan;
                                $isUserVip = $user->isStandard();

                                $tierWeight = [
                                    'free' => 0,
                                    'vip_week' => 1,
                                    'vip_month' => 2,
                                    'standard' => 3,
                                ];

                                $currentWeight = ($isUserVip && isset($tierWeight[$userPlan])) ? $tierWeight[$userPlan] : 0;
                                $planWeight = $tierWeight[$key] ?? 1;

                                $isCurrentPlan = ($isUserVip && $userPlan === $key);
                                $isLowerPlan = ($isUserVip && $planWeight < $currentWeight);
                            @endphp

                            @if($isCurrentPlan)
                                @if($key === 'standard')
                                    <button class="btn btn-block btn-lg btn-success font-weight-bold shadow disabled" disabled style="border-radius: 30px;">
                                        <i class="fas fa-check-circle"></i> {{ __('Gói của bạn (Vĩnh viễn)') }}
                                    </button>
                                @else
                                    <form method="POST" action="{{ route('payos.standard') }}" class="mb-0">
                                        @csrf
                                        <input type="hidden" name="plan" value="{{ $key }}">
                                        <button type="submit" class="btn btn-block btn-lg btn-success font-weight-bold shadow" style="border-radius: 30px;">
                                            <i class="fas fa-sync-alt"></i> {{ __('Gia hạn thêm') }} {{ $key === 'vip_week' ? '7 ' . __('ngày') : '30 ' . __('ngày') }}
                                        </button>
                                    </form>
                                @endif
                            @elseif($isLowerPlan)
                                <button class="btn btn-block btn-lg btn-secondary font-weight-bold disabled" disabled style="border-radius: 30px; opacity: 0.6;">
                                    <i class="fas fa-lock"></i> {{ __('Đang dùng gói cao hơn') }}
                                </button>
                            @else
                                <form method="POST" action="{{ route('payos.standard') }}" class="mb-0">
                                    @csrf
                                    <input type="hidden" name="plan" value="{{ $key }}">
                                    <button type="submit" class="btn btn-block btn-lg font-weight-bold shadow {{ $isPopular ? 'btn-warning text-dark pulse-gold' : 'btn-danger' }}" style="border-radius: 30px;">
                                        <i class="fas fa-bolt"></i> {{ $isUserVip ? __('Nâng cấp lên gói này') : __('Nâng cấp ngay') }}
                                    </button>
                                </form>
                            @endif
                        @else
                            <a href="{{ localized_url('login') }}?redirect={{ urlencode(url()->current()) }}" class="btn btn-block btn-lg btn-outline-warning font-weight-bold" style="border-radius: 30px;">
                                <i class="fas fa-sign-in"></i> {{ __('Đăng nhập để nâng cấp') }}
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Trust & Payment Info --}}
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card bg-dark text-white border-secondary p-4 shadow-sm" style="border-radius: 14px; background: #14141d !important;">
                <div class="row align-items-center">
                    <div class="col-lg-5 text-left mb-3 mb-lg-0">
                        <h5 class="text-gold font-weight-bold mb-2">
                            <i class="fas fa-shield-check text-success"></i> {{ __('Thanh toán an toàn qua VietQR / PayOS') }}
                        </h5>
                        <p class="text-muted small mb-0">
                            {{ __('Hệ thống hỗ trợ quét mã VietQR tự động qua mọi ứng dụng ngân hàng và ví điện tử tại Việt Nam. Kích hoạt VIP ngay lập tức sau 5 giây thanh toán thành công.') }}
                        </p>
                    </div>
                    <div class="col-lg-7 text-lg-right text-center">
                        <style>
                            .bank-logo-wrap {
                                display: inline-flex;
                                flex-wrap: wrap;
                                gap: 8px;
                                justify-content: center;
                                align-items: center;
                            }
                            .bank-logo-item {
                                background: #ffffff;
                                padding: 6px 10px;
                                border-radius: 8px;
                                display: inline-flex;
                                align-items: center;
                                justify-content: center;
                                box-shadow: 0 2px 6px rgba(0,0,0,0.3);
                                transition: transform 0.2s ease;
                                height: 38px;
                            }
                            .bank-logo-item:hover {
                                transform: translateY(-2px);
                                box-shadow: 0 4px 10px rgba(255,215,0,0.35);
                            }
                            .bank-logo-item img {
                                max-height: 26px;
                                width: auto;
                                display: block;
                            }
                        </style>
                        <div class="bank-logo-wrap justify-content-lg-end">
                            <div class="bank-logo-item" title="VietQR"><img src="{{ asset('img/banks/vietqr.svg') }}" alt="VietQR"></div>
                            <div class="bank-logo-item" title="Napas 247"><img src="{{ asset('img/banks/napas247.svg') }}" alt="Napas 247"></div>
                            <div class="bank-logo-item" title="Vietcombank"><img src="{{ asset('img/banks/vcb.png') }}" alt="Vietcombank"></div>
                            <div class="bank-logo-item" title="MBBank"><img src="{{ asset('img/banks/mb.png') }}" alt="MBBank"></div>
                            <div class="bank-logo-item" title="Techcombank"><img src="{{ asset('img/banks/tcb.png') }}" alt="Techcombank"></div>
                            <div class="bank-logo-item" title="BIDV"><img src="{{ asset('img/banks/bidv.png') }}" alt="BIDV"></div>
                            <div class="bank-logo-item" title="VietinBank"><img src="{{ asset('img/banks/icb.png') }}" alt="VietinBank"></div>
                            <div class="bank-logo-item" title="ACB"><img src="{{ asset('img/banks/acb.png') }}" alt="ACB"></div>
                            <div class="bank-logo-item" title="MoMo"><img src="{{ asset('img/banks/momo.svg') }}" alt="MoMo"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
