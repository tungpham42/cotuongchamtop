@extends('layouts.gamelayout')

@inject('puzzleService', 'App\Services\PuzzleService')
@php
    $puzzleName = $puzzleService->getNameByFen($fen);
@endphp

{{-- Set the dynamic Open Graph Image and Alt text using a placeholder service --}}
@if($puzzleName && !containsCJK($puzzleName))
    @section('og_image', 'https://placehold.co/1080x1080/DA251D/FFFF00/jpeg?font=roboto&text=' . urlencode(__("Thế cờ") . "\n" . $puzzleName))
    @section('og_image_alt', $puzzleName)
@else
    @section('og_image', url('/') . '/img/co-the.jpg')
    @section('og_image_alt', 'Cờ thế')
@endif
@section('og_image_width', '1080')
@section('og_image_height', '1080')
@section('og_image_type', 'image/jpeg')

@section('aboveBoard')
    <h5 class="text-center my-1" data-toggle="tooltip" data-placement="top" title="{{ __("Bạn đang giải cờ thế với máy") }}">
        {{ __("Bạn đang giải") }} @if($puzzleName) "{{ $puzzleName }}" @else cờ thế @endif
    </h5>
    @include('layouts.partials.ai.gameStatus')
@endsection

@section('rightSide')
    @include('layouts.partials.ai.panelTop')
    @include('layouts.partials.ai.hostControls', [
        'showSwitch' => isset($name) && $name != '',
        'switchIcon' => 'fa-chess-board',
    ])
@endsection

@section('belowContent')
    @include('layouts.partials.ai.controlButtons', [
        'firstStep' => 1,
        'resetStep' => 4,
        'secondary' => [
            'step'  => 3,
            'intro' => 'Ấn vào đây để xếp cờ thế',
            'class' => 'w-25 btn btn-dark btn-lg showPromotion',
            'href'  => url('/co-the'),
            'icon'  => 'fa-puzzle-piece',
            'label' => __("Xếp cờ"),
        ],
    ])
    @include('layouts.partials.ai.copyUrl')

    @include('layouts.partials.kypho')

    {{-- The puzzle's best-move request has never carried a level, so keep it that way. --}}
    @include('layouts.partials.ai.gameScript', [
        'startFen'          => $fen,
        'level'             => $level,
        'sendLevelWithMove' => false,
        'autoStart'         => ['delay' => 0, 'ifBlackToMove' => true],
    ])
    @include('layouts.partials.ai.gameLists')
@endsection
