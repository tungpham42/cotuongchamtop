<footer>
  <div class="container">
    <div class="row py-5 px-0">
      <div class="col-12 col-xl-3 col-lg-3 col-md-6 col-sm-12 mb-3 vcard">
        <p>Email liên hệ</p>
        <a class="w-100 email showPromotion" href="mailto:tung.42@gmail.com">tung.42@gmail.com</a>
        <p class="mt-3">Bản quyền <i class="fal fa-copyright"></i> {{ date('Y') }} <a class="url fn h-card showPromotion" target="_blank" href="https://tungpham42.github.io/">Phạm Tùng</a></p>
        <div class="bg-white p-1" style="width: fit-content; border-radius: 0.5rem;"><a href="https://soft.io.vn" target="_blank"><img alt="Logo Soft" height="40" class="me-2" src="{{ url('') }}/img/soft-logo.webp"></a></div>
      </div>
      <div class="col-12 col-xl-3 col-lg-3 col-md-6 col-sm-12 mb-3">
        <ul class="list-unstyled">
          <li>
            <a class="home showPromotion" href="{{ url('') }}"><i class="fal fa-home-lg-alt"></i> Trang chủ</a>
          </li>
          <li>
            <a class="dashboard showPromotion" href="{{ url('/thi-dau') }}"><i class="fal fa-trophy-alt"></i> Thi đấu</a>
          </li>
          <li>
            <a class="trophy showPromotion" href="{{ url('/bang-xep-hang') }}"><i class="fal fa-star"></i> Bảng xếp hạng</a>
          </li>
          <li>
            <a class="room showPromotion rooms-list" href="{{ url('/sanh-cho') }}"><i class="fal fa-list-alt"></i> Sảnh chờ</a>
          </li>
          <li>
            <a class="setup puzzle showPromotion" href="{{ url('/co-the') }}"><i class="fal fa-puzzle-piece"></i> Cờ thế</a>
          </li>
          <li>
            <a class="about showPromotion" href="{{ url('/gioi-thieu') }}"><i class="fal fa-info-square"></i> Giới thiệu</a>
          </li>
          <li>
            <a class="contact showPromotion" href="{{ url('/lien-he') }}"><i class="fal fa-envelope"></i> Liên hệ</a>
          </li>
          <li>
            <a target="_blank" class="game showPromotion" href="https://game.cotuong.top"><i class="fal fa-gamepad-alt"></i> Trò chơi</a>
          </li>
          <li>
            <a target="_blank" class="chat showPromotion" href="https://chat.cotuong.top"><i class="fal fa-comment-lines"></i> Chatbox</a>
          </li>
          <li>
            <a target="_blank" class="buy showPromotion" href="https://www.codester.com/items/41601/multilingual-chinese-chess-game-with-many-options?ref=tungpham"><i class="fal fa-shopping-cart"></i> Mua mã nguồn</a>
          </li>
          <li>
            <a target="_blank" class="2048 showPromotion" href="https://nhipsinhhoc.vn/"><i class="fal fa-head-side-medical"></i> Nhịp Sinh học</a>
          </li>
          <li>
            <a target="_blank" class="hololab showPromotion" href="https://hololab.vn/"><i class="fal fa-cube"></i> Hologram</a>
          </li>
          @if (isset($room->host_id))
          <li>
            <a class="lang showPromotion" href="{{ url('/en') }}"><span class="fi fi-us"></span> English</a>
          </li>
          <li>
            <a class="lang showPromotion" href="{{ url('/ja') }}"><span class="fi fi-jp"></span> 日本語</a>
          </li>
          <li>
            <a class="lang showPromotion" href="{{ url('/ko') }}"><span class="fi fi-kr"></span> 한국어</a>
          </li>
          <li>
            <a class="lang showPromotion" href="{{ url('/zh') }}"><span class="fi fi-cn"></span> 中文</a>
          </li>
          @else
          <li>
            <a class="lang showPromotion" href="{{ url($langEnUrl) }}"><span class="fi fi-us"></span> English</a>
          </li>
          <li>
            <a class="lang showPromotion" href="{{ url($langJaUrl) }}"><span class="fi fi-jp"></span> 日本語</a>
          </li>
          <li>
            <a class="lang showPromotion" href="{{ url($langKoUrl) }}"><span class="fi fi-kr"></span> 한국어</a>
          </li>
          <li>
            <a class="lang showPromotion" href="{{ url($langZhUrl) }}"><span class="fi fi-cn"></span> 中文</a>
          </li>
          @endif
        </ul>
      </div>
      <div class="col-12 col-xl-3 col-lg-3 col-md-6 col-sm-12 mb-3">
        <p>Chúng tôi trên mạng xã hội</p>
        <a class="w-100 mr-2 display-4 showPromotion" target="_blank" href="https://www.youtube.com/@CoTuongVlog/videos"><i class="fab fa-youtube"></i></a>
        <a class="w-100 mr-2 display-4 showPromotion" target="_blank" href="https://www.facebook.com/CoTuongPage/"><i class="fab fa-facebook-square rounded"></i></a>
        <a class="w-100 mr-2 display-4 showPromotion" target="_blank" href="https://www.linkedin.com/company/cotuong/"><i class="fab fa-linkedin rounded"></i></a>
      </div>
      <div class="col-12 col-xl-3 col-lg-3 col-md-6 col-sm-12 mb-3">
        <p>Đã xác thực HTML5 và CSS3</p>
        <a title="Valid HTML5" class="w-100 mr-2 display-4 text-decoration-none showPromotion" target="_blank" href="https://validator.w3.org/check?uri=referer">
          <i class="fab fa-html5"></i>
        </a>
        <a title="Valid CSS3" class="w-100 mr-2 display-4 text-decoration-none showPromotion" target="_blank" href="https://jigsaw.w3.org/css-validator/check/referer">
          <i class="fab fa-css3-alt"></i>
        </a>
      </div>
    </div>
  </div>
</footer>
<script>
// if (window.location.pathname == '/') {
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
$('.stopPromotion').each(function(){
  // if (removeTrailingSlash($(this).attr('href')) == removeTrailingSlash(window.location.href) || $(this).attr('href') == window.location.href) {
  //   $(this).css({'cursor': 'default', 'pointer-events': 'none'});
  //   return false;
  // }
  $(this).on('click auxclick', function(e){
    // if (removeTrailingSlash($(this).attr('href')) !== removeTrailingSlash(window.location.href)) {
    //   e.preventDefault();
    //   $('#AdSenseModal').attr('data-url', $(this).attr('href')).modal('show');
    // } else {
      window.location.href = $(this).attr('href');
    // }
  });
});
$('#AdSenseModal').on('show.bs.modal', function(){
  if (!$('#AdSenseModal').find('ins').attr('data-ad-status')) {
    $('#AdSenseModal').find('ins').attr('data-ad-status', 'unfilled');
  }
}).on('shown.bs.modal', function() {
  $('#adModalCloseBtn').attr('data-original-title', 'Đi đến: ' + $(this).attr('data-url')).css('cursor', 'wait').prop('disabled', true);
  $('#adModalCloseBtn').tooltip();
  let i = 2;
  let timer = setInterval(function() {
    console.log(--i);
    $('#adModalCloseBtn').find('span').text(i + ' giây');
    if (i === -1) {
      $('#adModalCloseBtn').find('i').removeClass('fa-clock').addClass('fa-link');
      $('#adModalCloseBtn').css('cursor', 'pointer').removeClass('disabled').removeAttr('disabled').addClass('pulse-red').find('span').text('Đến ngay');
      clearInterval(timer);
    }
  }, 1000);
}).on('hidden.bs.modal', function() {
  $('#adModalCloseBtn').find('span').text('2 giây');
  // window.open($(this).attr('data-url'), '_blank');
  window.location.href = $(this).attr('data-url');
});
$(function () {
  $('.btn-dark').each(function(){
    $(this).on('mouseenter mouseleave', function(){
      $(this).toggleClass('btn-dark btn-danger');
    });
  });
  $('.game .btn, .about .btn, .puzzles .btn').each(function(){
    $(this).on('mouseenter mouseleave', function(){
      $(this).find('i').toggleClass('fad fas');
    });
  });
});
</script>
<a href="#0" class="cd-top js-cd-top rounded" style="background-image: url('{{ url('/') }}/img/cd-top-arrow.svg');">Top</a>
<script src="{{ URL::to('') }}/js/to-top.js"></script>
@include('cookie-consent::index')