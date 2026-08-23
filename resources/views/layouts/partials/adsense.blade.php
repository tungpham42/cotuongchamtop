@if ($showAds ?? true)
<div style="background-color: #302E2B" class="container-fluid adsense px-0">
  <div class="row w-100 mx-auto">
    <h2 class="d-none mb-4 mx-auto w-100 text-light text-center"><i class="fas fa-ad"></i> Quảng cáo</h2>
    <!-- CO_res -->
    <ins class="adsbygoogle w-100"
        style="display:block"
        data-ad-client="ca-pub-3585118770961536"
        data-ad-slot="7831723879"
        data-ad-format="auto"
        data-full-width-responsive="true"></ins>
    <a class="w-100 hoc-link" href="https://www.facebook.com/groups/HoiChoiCoTuong"><img title="{{ __("Cờ tướng") }}" alt="{{ __("Cờ tướng") }}" class="w-100 h-auto mx-auto d-block" src="{{ $cdnUrl }}/img/xiangqi-board.jpg" ></a>
    <script>
    (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
  </div>
</div>
@endif
