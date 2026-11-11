{{--
    "Play online" dropdown + side switch + volume/tour buttons.

    Params:
      showSwitch  (bool, default true)         render the "Đổi bên" button
      switchIcon  (string, default 'fa-sync')  Font Awesome icon class for it
--}}
@php
    $showSwitch = $showSwitch ?? true;
    $switchIcon = $switchIcon ?? 'fa-sync';
@endphp
<div class="dropup mx-auto text-center my-1">
    <button class="btn btn-danger btn-lg dropdown-toggle" type="button" id="hostDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span data-toggle="tooltip" data-placement="top" title="{{ __("Đấu với bạn bè trong phòng") }}"><i class="fad fa-gamepad-alt"></i> {{ __("Chơi online") }}</span>
    </button>
    @if ($showSwitch)
        <a id="switch" class="btn btn-dark btn-lg mx-auto"><i class="fad {{ $switchIcon }}"></i> {{ __("Đổi bên") }}</a>
    @endif
    @include('common.volumeBtn')
    @include('common.tourBtn')
    <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="hostDropdown" id="tao-phong" data-phong="{{ md5(time()) }}" data-url="{{ localized_url('room.host', ['code' => md5(time())]) }}">
        @if (!auth()->check())
            <a data-toggle="tooltip" data-placement="bottom" title="{{ __("Đăng nhập để tham gia thi đấu") }}" class="dropdown-item thi-dau" style="cursor: pointer !important;" href="{{ localized_url('login') }}"><i class="fas fa-sign-in text-dark"></i> {{ __("Đăng nhập") }}</a>
        @else
            <a data-toggle="tooltip" data-placement="bottom" title="{{ __("Thi đấu tính điểm và xếp hạng") }}" id="create-room" class="dropdown-item thi-dau" style="cursor: pointer !important;" href="javascript:createRoom();"><i class="fas fa-trophy-alt text-dark"></i> {{ __("Thi đấu") }}</a>
        @endif
        <a data-toggle="tooltip" data-placement="bottom" title="Chơi cần mật khẩu" id="tao-phong-private" class="dropdown-item" style="cursor: pointer !important;"><i class="fas fa-lock text-dark"></i> {{ __("Riêng tư") }}</a>
        @if ($randomRoom != null)
            <a data-toggle="tooltip" data-placement="bottom" title="Chơi trong phòng Công khai ngẫu nhiên" id="random-room" class="dropdown-item" style="cursor: pointer !important;" href="{{ localized_url('room.random', ['code' => $randomRoom['code'] ]) }}"><i class="fas fa-random text-dark"></i> {{ __("Ngẫu nhiên") }}</a>
        @endif
        <a data-toggle="tooltip" data-placement="bottom" title="Tìm phòng trống" id="room-list" class="dropdown-item rooms-list" style="cursor: pointer !important;" href="{{ localized_url('room.list') }}"><i class="fas fa-list-alt text-dark"></i> {{ __("Sảnh chờ") }}</a>
    </div>
</div>
