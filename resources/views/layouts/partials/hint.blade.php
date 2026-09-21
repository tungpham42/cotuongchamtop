@auth
<div class="hint-widget mx-auto text-center my-1" id="hint-widget">
    <a data-step="6"
        data-intro="{{ __('Ấn vào đây để xem trước những nước đi máy gợi ý') }}"
        id="hint-btn"
        class="btn btn-dark btn-lg"
        data-toggle="tooltip" data-placement="top"
        >
        <i class="fad fa-lightbulb-on"></i> {{ __('Gợi ý') }}
    </a>
</div>
@endauth

@guest
<div class="hint-widget mx-auto text-center my-1" id="hint-widget">
    <a id="hint-btn-guest"
        href="javascript:void(0)"
        class="btn btn-dark btn-lg"
        onclick="var n = document.getElementById('hint-login-notice'); n.classList.remove('d-none'); clearTimeout(window._hintNoticeTimer); window._hintNoticeTimer = setTimeout(function () { n.classList.add('d-none'); }, 3000);"
        >
        <i class="fad fa-lightbulb-on"></i> {{ __('Gợi ý') }}
    </a>
    <div id="hint-login-notice" class="alert alert-dark d-none mt-2 mb-0" role="alert">
        <i class="fad fa-lock"></i> {{ __('Đăng nhập để xem gợi ý') }}
    </div>
</div>
@endguest
