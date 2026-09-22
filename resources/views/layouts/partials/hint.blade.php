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
        >
        <i class="fad fa-lightbulb-on"></i> {{ __('Gợi ý') }}
    </a>
    <div id="hint-login-notice" class="alert alert-dark mt-2 mb-0" role="alert">
        <a href="{{ localized_url('login') }}"><i class="fad fa-lock"></i> {{ __('Đăng nhập để xem gợi ý') }}</a>
    </div>
</div>
@endguest
