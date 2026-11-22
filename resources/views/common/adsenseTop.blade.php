@if ($showAds ?? true)
<div class="adsense-full-bleed my-0 p-0 text-center" style="min-height: 100px;">
    <ins class="adsbygoogle"
         style="display:block; width:100%;"
         data-ad-client="ca-pub-3585118770961536"
         data-ad-slot="9745772897"
         data-ad-format="auto"
         data-full-width-responsive="true"></ins>
    <script>
         (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
</div>
<style>
.adsense-full-bleed {
    width: 100vw;
    max-width: 100vw;
    position: relative;
    left: 50%;
    right: 50%;
    margin-left: -50vw;
    margin-right: -50vw;
    overflow-x: clip;
}
</style>
@endif
