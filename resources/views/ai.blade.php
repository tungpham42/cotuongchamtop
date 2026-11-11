@extends('layouts.gamelayout')

@section('og_image')
@include('layouts.partials.ai.ogImageUrl')
@endsection

@section('aboveBoard')
    @php
        $actionMap = [
            '1' => 'tập chơi',
            '2' => 'thư giãn',
            '3' => 'chơi',
            '4' => 'đấu',
            '5' => 'đấu trí',
            '8' => 'khiêu chiến',
        ];

        $action = $actionMap[$level ?? '3'] ?? 'chơi';
    @endphp

    @if(isset($level) && $level == '8')
        {{-- GRANDMASTER CUSTOM UI --}}
        <div class="grandmaster-header text-center mt-3 mb-2 p-3 rounded" style="background: linear-gradient(45deg, #1a0505, #3a0000); border: 2px solid #ffd700; color: #ffd700; box-shadow: 0 0 20px rgba(255, 215, 0, 0.4);">
            <img src="/img/xiangqipieces/wiki/rK.svg" width="55" class="mb-2" style="filter: drop-shadow(0 0 8px #ffd700);" alt="Grandmaster">
            <h4 class="text-uppercase font-weight-bold mb-1"><i class="fas fa-crown"></i> {{ __("Đại Kiện Tướng") }}</h4>
            <p class="m-0 small text-light">{{ __("Bạn đang khiêu chiến với đối thủ mạnh nhất.") }}</p>
        </div>
    @else
        {{-- STANDARD AI UI --}}
        <h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="{{ __("Cấp độ") }}: {{ __($levelTxt) }}">{{ __('Bạn đang') }} {{ __($action) }} {{ __('với máy') }}<span id="puzzle-title"></span></h5>
    @endif
    @include('layouts.partials.ai.gameStatus')
@endsection

@section('rightSide')
    @include('layouts.partials.ai.panelTop')
    @include('layouts.partials.ai.levelDropdown', [
        'routePrefix' => 'ai',
        'buttonLabel' => __("Chọn cấp độ máy"),
        'withMaster'  => true,
    ])
    @include('layouts.partials.ai.hostControls')
@endsection

@section('belowContent')
    @include('layouts.partials.ai.controlButtons', [
        'firstStep' => 2,
        'resetStep' => 4,
        'secondary' => [
            'step'  => 5,
            'intro' => 'Nơi luyện tập với chính mình nhé',
            'class' => 'w-25 btn btn-dark btn-lg showPromotion',
            'href'  => url('/' . __('choi-mot-minh')),
            'icon'  => 'fa-user',
            'label' => __("Một mình"),
        ],
    ])

    @include('layouts.partials.kypho')

    @include('layouts.partials.ai.gameScript', [
        'startFen'  => 'start',
        'level'     => $level ?? 3,
        'autoStart' => (isset($computerStarts) && $computerStarts) ? ['delay' => 1000, 'ifBlackToMove' => false] : null,
    ])
    @if(isset($level) && $level != '8')
        @include('layouts.partials.ai.gameLists')
    @endif
@endsection
