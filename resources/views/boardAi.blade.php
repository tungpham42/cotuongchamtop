@extends('layouts.gamelayout')

@section('og_image')
@include('layouts.partials.ai.ogImageUrl')
@endsection

@section('aboveBoard')
    <h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="{{ __("Cấp độ") }}: {{ $levelTxt }}">{{ __("Bạn đang giải") }}<span id="puzzle-title"></span> {{ __("với máy") }}</h5>
    @include('layouts.partials.ai.gameStatus')
@endsection

@section('rightSide')
    @include('layouts.partials.ai.panelTop')
    @include('layouts.partials.ai.levelDropdown', [
        'routePrefix'     => 'board.ai',
        'buttonLabel'     => __("Chọn cấp độ bàn cờ"),
        'matchByLevelTxt' => true,
        'carriesFen'      => true,
    ])
    @include('layouts.partials.ai.hostControls')
@endsection

@section('belowContent')
    @include('layouts.partials.ai.copyUrl')
    @include('layouts.partials.ai.controlButtons', [
        'firstStep' => 2,
        'resetStep' => 5,
        'secondary' => [
            'id'    => 'board',
            'step'  => 4,
            'intro' => 'Ấn vào đây để tự giải bàn cờ',
            'class' => 'add-fen w-25 btn btn-dark btn-lg',
            'href'  => url('/ban-co'),
            'icon'  => 'fa-user',
            'label' => __("Tự giải"),
        ],
    ])

    @include('layouts.partials.kypho')

    @include('layouts.partials.ai.gameScript', [
        'startFen'  => $fen,
        'level'     => $level,
        'autoStart' => ['delay' => 0, 'ifBlackToMove' => true],
    ])
    @include('layouts.partials.ai.gameLists')
@endsection
