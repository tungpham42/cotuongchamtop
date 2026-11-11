<div class="js-cookie-consent cookie-consent" role="region" aria-label="{{ __('Thông báo về cookie') }}">
    <div class="container cookie-consent__inner">
        <p class="cookie-consent__message mb-0">
            {!! trans('cookie-consent::texts.message') !!}
            <a class="cookie-consent__link" href="{{ localized_url('privacy') }}">{{ __('Chính sách bảo mật') }}</a>
        </p>
        <div class="cookie-consent__actions">
            <button type="button" class="btn btn-outline-light btn-sm js-cookie-consent-reject cookie-consent__reject">{{ __('Từ chối') }}</button>
            <button type="button" class="btn btn-danger btn-sm js-cookie-consent-agree cookie-consent__agree">{{ trans('cookie-consent::texts.agree') }}</button>
        </div>
    </div>
</div>
