@if ($showAds ?? true)
{{-- AdSense Top Banner (styles: public/css/ads-cozy.css) --}}
<aside class="ad-slot ad-slot--top mt-3" aria-label="{{ __('Quảng cáo') }}">
    <div class="ad-slot__frame">
        <span class="ad-slot__label">{{ __('Quảng cáo') }}</span>
        <div class="ad-slot__body">
            <ins class="adsbygoogle"
                    style="display: block; width: 100%; max-width: 100%; min-width: 100%"
                    data-ad-client="ca-pub-3585118770961536"
                    data-ad-slot="9745772897"
                    data-ad-format="auto"
                    data-full-width-responsive="true"></ins>
            <script>
                    (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
        </div>
    </div>
</aside>
@endif
