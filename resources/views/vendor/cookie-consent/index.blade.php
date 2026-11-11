@if($cookieConsentConfig['enabled'] && ! $alreadyConsentedWithCookies)

    @include('cookie-consent::dialogContents')

    <script>

        window.laravelCookieConsent = (function () {

            const ACCEPTED = 1;
            const REJECTED = 0;
            // A rejection is asked again after a while; an acceptance is kept for the configured lifetime.
            const REJECT_LIFETIME_DAYS = 180;
            const COOKIE_NAME = '{{ $cookieConsentConfig['cookie_name'] }}';
            const COOKIE_DOMAIN = '{{ config('session.domain') ?? request()->getHost() }}';

            function consentWithCookies() {
                saveChoice(ACCEPTED, {{ $cookieConsentConfig['cookie_lifetime'] }});
            }

            function rejectCookies() {
                saveChoice(REJECTED, REJECT_LIFETIME_DAYS);
            }

            function saveChoice(value, lifetimeInDays) {
                setCookie(COOKIE_NAME, value, lifetimeInDays);
                updateConsentMode(value);
                hideCookieDialog();
            }

            // Tell Google tags (see common/head.blade.php) about the visitor's choice.
            function updateConsentMode(value) {
                if (typeof gtag !== 'function') {
                    return;
                }

                const state = value === ACCEPTED ? 'granted' : 'denied';

                gtag('consent', 'update', {
                    ad_storage: state,
                    ad_user_data: state,
                    ad_personalization: state,
                    analytics_storage: state
                });
            }

            // True once the visitor has made any choice, accepted or rejected.
            function choiceExists(name) {
                return document.cookie.split('; ').some(function (cookie) {
                    return cookie === name + '=' + ACCEPTED || cookie === name + '=' + REJECTED;
                });
            }

            function hideCookieDialog() {
                const dialogs = document.getElementsByClassName('js-cookie-consent');

                for (let i = 0; i < dialogs.length; ++i) {
                    dialogs[i].style.display = 'none';
                }
            }

            function setCookie(name, value, expirationInDays) {
                const date = new Date();
                date.setTime(date.getTime() + (expirationInDays * 24 * 60 * 60 * 1000));
                document.cookie = name + '=' + value
                    + ';expires=' + date.toUTCString()
                    + ';domain=' + COOKIE_DOMAIN
                    + ';path=/{{ config('session.secure') ? ';secure' : null }}'
                    + '{{ config('session.same_site') ? ';samesite='.config('session.same_site') : null }}';
            }

            if (choiceExists(COOKIE_NAME)) {
                hideCookieDialog();
            }

            const agreeButtons = document.getElementsByClassName('js-cookie-consent-agree');

            for (let i = 0; i < agreeButtons.length; ++i) {
                agreeButtons[i].addEventListener('click', consentWithCookies);
            }

            const rejectButtons = document.getElementsByClassName('js-cookie-consent-reject');

            for (let i = 0; i < rejectButtons.length; ++i) {
                rejectButtons[i].addEventListener('click', rejectCookies);
            }

            return {
                consentWithCookies: consentWithCookies,
                rejectCookies: rejectCookies,
                hideCookieDialog: hideCookieDialog
            };
        })();
    </script>

@endif
