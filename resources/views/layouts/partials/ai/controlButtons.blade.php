{{--
    Resign / Undo row and the "secondary + Reset" row.

    Params:
      firstStep   intro.js step of "Resign" ("Undo" gets firstStep + 1)
      secondary   array: step, intro, class, href, icon, label, [id]   -- the page-specific button
      resetStep   intro.js step of "Reset"
--}}
<p class="w-100 text-center mt-0 mb-1">
    <a data-step="{{ $firstStep }}" data-intro="Ấn vào đây nếu bạn không biết đi nước nào" id="resign" class="w-25 btn btn-dark btn-lg"><i class="fad fa-flag"></i> {{ __("Bỏ cuộc") }}</a>
    <a data-step="{{ $firstStep + 1 }}" data-intro="Ấn vào đây để quay lại nước trước đó" id="undo" class="w-25 btn btn-dark btn-lg"><i class="fad fa-undo-alt"></i> {{ __("Đi lại") }}</a>
</p>
<p class="w-100 text-center mt-0 mb-1">
    <a data-step="{{ $secondary['step'] }}" data-intro="{{ $secondary['intro'] }}" @if (!empty($secondary['id'])) id="{{ $secondary['id'] }}" @endif class="{{ $secondary['class'] }}" href="{{ $secondary['href'] }}"><i class="fad {{ $secondary['icon'] }}"></i> {{ $secondary['label'] }}</a>
    <a data-step="{{ $resetStep }}" data-intro="Ấn vào đây để {{ __("chơi") }} lại từ đầu" id="reset" class="w-25 btn btn-dark btn-lg"><i class="fad fa-redo-alt"></i> {{ __("Chơi lại") }}</a>
</p>
