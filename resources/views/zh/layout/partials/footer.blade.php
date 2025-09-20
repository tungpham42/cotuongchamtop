<footer>
  <div class="container">
    <div class="row py-5 px-0">
      <div class="col-12 col-xl-3 col-lg-3 col-md-6 col-sm-12 mb-3 vcard">
        <p>联系电子邮件</p>
        <a class="w-100 email stopPromotion" href="mailto:tung.42@gmail.com">tung.42@gmail.com</a>
        <p class="mt-3">版权 <i class="fal fa-copyright"></i> {{ date('Y') }} <a class="url fn h-card stopPromotion" target="_blank" href="https://tungpham42.github.io/">Tung Pham</a></p>
        <div class="bg-white p-1" style="width: fit-content; border-radius: 0.5rem;"><a href="https://soft.io.vn" target="_blank"><img alt="Logo Soft" height="40" class="me-2" src="{{ url('') }}/img/soft-logo.webp"></a></div>
      </div>
      <div class="col-12 col-xl-3 col-lg-3 col-md-6 col-sm-12 mb-3">
        <ul class="list-unstyled">
          <li>
            <a class="home stopPromotion" href="{{ url('/zh') }}"><i class="fal fa-home-lg-alt"></i> 主页</a>
          </li>
          <li>
            <a class="room stopPromotion rooms-list" href="{{ url('/fangjianliebiao') }}"><i class="fal fa-list-alt"></i> 房间列表</a>
          </li>
          <li>
            <a class="setup puzzle stopPromotion" href="{{ url('/mi') }}"><i class="fal fa-puzzle-piece"></i> 谜</a>
          </li>
          <li>
            <a class="about stopPromotion" href="{{ url('/guanyuwomens') }}"><i class="fal fa-info-square"></i> 关于我们</a>
          </li>
          <li>
            <a class="contact stopPromotion" href="{{ url('/lianxiwomen') }}"><i class="fal fa-envelope"></i> 联系我们</a>
          </li>
          <li>
            <a target="_blank" class="game stopPromotion" href="https://game.cotuong.top"><i class="fal fa-gamepad-alt"></i> 游戏</a>
          </li>
          <li>
            <a target="_blank" class="chat stopPromotion" href="https://chat.cotuong.top"><i class="fal fa-comment-lines"></i> 聊天框</a>
          </li>
          <li>
            <a target="_blank" class="buy stopPromotion" href="https://www.codester.com/items/41601/multilingual-chinese-chess-game-with-many-options?ref=tungpham"><i class="fal fa-shopping-cart"></i> 购买</a>
          </li>
          <li>
            <a target="_blank" class="2048 stopPromotion" href="https://nhipsinhhoc.vn/zh/"><i class="fal fa-head-side-medical"></i> 生理节律</a>
          </li>
          <li>
            <a class="lang stopPromotion" href="{{ url($langViUrl) }}"><span class="fi fi-vn"></span> Tiếng Việt</a>
          </li>
          <li>
            <a class="lang stopPromotion" href="{{ url($langEnUrl) }}"><span class="fi fi-us"></span> English</a>
          </li>
          <li>
            <a class="lang stopPromotion" href="{{ url($langJaUrl) }}"><span class="fi fi-jp"></span> 日本語</a>
          </li>
          <li>
            <a class="lang stopPromotion" href="{{ url($langKoUrl) }}"><span class="fi fi-kr"></span> 한국어</a>
          </li>
        </ul>
      </div>
      <div class="col-12 col-xl-3 col-lg-3 col-md-6 col-sm-12 mb-3">
        <p>在社交媒体上查找我们</p>
        <a class="w-100 mr-2 display-4 stopPromotion" target="_blank" href="https://www.youtube.com/@CoTuongVlog/videos"><i class="fab fa-youtube"></i></a>
        <a class="w-100 mr-2 display-4 stopPromotion" target="_blank" href="https://www.facebook.com/CoTuongPage/"><i class="fab fa-facebook-square rounded"></i></a>
        <a class="w-100 mr-2 display-4 stopPromotion" target="_blank" href="https://www.linkedin.com/company/cotuong/"><i class="fab fa-linkedin rounded"></i></a>
      </div>
      <div class="col-12 col-xl-3 col-lg-3 col-md-6 col-sm-12 mb-3">
        <p>通过HTML5和CSS3验证</p>
        <a title="Valid HTML5" class="w-100 mr-2 display-4 text-decoration-none stopPromotion" target="_blank" href="https://validator.w3.org/check?uri=referer">
          <i class="fab fa-html5"></i>
        </a>
        <a title="Valid CSS3" class="w-100 mr-2 display-4 text-decoration-none stopPromotion" target="_blank" href="https://jigsaw.w3.org/css-validator/check/referer">
          <i class="fab fa-css3-alt"></i>
        </a>
      </div>
    </div>
  </div>
</footer>
<script>
// if (window.location.pathname == '/zh') {
//   $('#ShopeeModal').modal('show');
// }
$('#undo, #reset, #resign').each(function(){
  $(this).on('click', function(){
    window.scrollTo({ top: 0 });
  });
});
$('.menu-toggle').on('click', function(){
  $(this).toggleClass('open close');
});
if (!$('#ads').find('ins').attr('data-ad-status')) {
  $('#ads').find('ins').attr('data-ad-status', 'unfilled');
}
if (!$('#topAds').find('ins').attr('data-ad-status')) {
  $('#topAds').find('ins').attr('data-ad-status', 'unfilled');
}
if (!$('#sideAds').find('ins').attr('data-ad-status')) {
  $('#sideAds').find('ins').attr('data-ad-status', 'unfilled');
}
$('.showPromotion').each(function(){
  $(this).on('click auxclick', function(e){
    if (removeTrailingSlash($(this).attr('href')) !== removeTrailingSlash(window.location.href)) {
      e.preventDefault();
      $('#AdSenseModal').attr('data-url', $(this).attr('href')).modal('show');
    } else {
      window.location.href = $(this).attr('href');
    }
  });
});
$('#AdSenseModal').on('show.bs.modal', function(){
  if (!$('#AdSenseModal').find('ins').attr('data-ad-status')) {
    $('#AdSenseModal').find('ins').attr('data-ad-status', 'unfilled');
  }
}).on('shown.bs.modal', function() {
  $('#adModalCloseBtn').attr('data-original-title', '前往：' + $(this).attr('data-url')).css('cursor', 'wait').prop('disabled', true);
  $('#adModalCloseBtn').tooltip();
  let i = 2;
  let timer = setInterval(function() {
    console.log(--i);
    $('#adModalCloseBtn').find('span').text(i + '秒');
    if (i === -1) {
      $('#adModalCloseBtn').find('i').removeClass('fa-clock').addClass('fa-link');
      $('#adModalCloseBtn').css('cursor', 'pointer').removeClass('disabled').removeAttr('disabled').addClass('pulse-red').find('span').text('我们走吧');
      clearInterval(timer);
    }
  }, 1000);
}).on('hidden.bs.modal', function() {
  $('#adModalCloseBtn').find('span').text('2秒');
  // window.open($(this).attr('data-url'), '_blank');
  window.location.href = $(this).attr('data-url');
});
$(function () {
  $('.btn-dark').each(function(){
    $(this).on('mouseenter mouseleave', function(){
      $(this).toggleClass('btn-dark btn-danger');
    });
  });
  $('.game .btn, .about .btn').each(function(){
    $(this).on('mouseenter mouseleave', function(){
      $(this).find('i').toggleClass('fad fas');
    });
  });
});
</script>
<a href="#0" class="cd-top js-cd-top rounded" style="background-image: url('{{ $cdnUrl }}/img/cd-top-arrow.svg');">Top</a>
<script src="{{ URL::to('') }}/js/to-top.js"></script>
@include('cookie-consent::index')