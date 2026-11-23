@if ($showAds ?? true)
{{-- AdSense Bottom (styles: public/css/ads-cozy.css) --}}
<aside class="ad-slot ad-slot--bottom" aria-label="{{ __('Quảng cáo') }}">
    <div class="ad-slot__frame">
        <span class="ad-slot__label">{{ __('Quảng cáo') }}</span>
        <div class="ad-slot__body">
            <ins class="adsbygoogle"
                    style="display:block"
                    data-ad-client="ca-pub-3585118770961536"
                    data-ad-slot="4826217193"
                    data-ad-format="auto"
                    data-full-width-responsive="true"></ins>
            <script>
                    (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
        </div>
    </div>
</aside>
@endif
