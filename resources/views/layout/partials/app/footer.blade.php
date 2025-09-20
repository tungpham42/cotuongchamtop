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
$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});
var locale = {
  OK: '<i class="fas fa-check"></i> Đồng ý',
  CONFIRM: '<i class="fas fa-check"></i> Chấp nhận',
  CANCEL: '<i class="fas fa-times"></i> Hủy'
};
bootbox.addLocale('vi', locale);
function showLatestRoom(offset, newCode) {
  $.ajax({
    type: "POST",
    url: '{{ url('/api') }}/getLatestRoom',
    data: {
      'offset': offset
    },
    dataType: "json"
  }).done(function(data){
    if (data.room.code != newCode) {
      var htmlContent = `
        <button id="join-room" class="btn btn-lg btn-danger float-right ml-2"><i class="fas fa-sign-in-alt"></i> Vào</button>
        <button id="cancel-room" class="btn btn-lg btn-dark float-right"><i class="fas fa-times"></i> Hủy</button>
      `;
      var dialog = bootbox.dialog({
        title: 'Bạn được thách đấu tại "' + data.room.name + '"!',
        message: htmlContent,
        locale: 'vi',
        size: 'small',
        centerVertical: true,
        closeButton: false
      });
      dialog.find("#join-room").on('click', function() {
        if (data.color == 'red') {
          dialog.modal("hide");
          // $('#AdSenseModal').attr('data-url', "{{ url('/') }}" + '/phong/' + data.room.code + '/do').modal('show');
          // $('#AdSenseModal').on('shown.bs.modal', function (event) {
          //   $('#adModalCloseBtn').attr('data-original-title', 'Mời Đỏ đi tiếp trong phòng "' + data.room.name + '"');
          //   $('#adModalCloseBtn').tooltip();
          // })
          window.location.href = "{{ url('/') }}" + '/phong/' + data.room.code + '/do';
        } else if (data.color == 'black') {
          dialog.modal("hide");
          // $('#AdSenseModal').attr('data-url', "{{ url('/') }}" + '/phong/' + data.room.code + '/den').modal('show');
          // $('#AdSenseModal').on('shown.bs.modal', function (event) {
          //   $('#adModalCloseBtn').attr('data-original-title', 'Mời Đen đi tiếp trong phòng "' + data.room.name + '"');
          //   $('#adModalCloseBtn').tooltip();
          // })
          window.location.href = "{{ url('/') }}" + '/phong/' + data.room.code + '/den';
        }
      });

      // Handle "Cancel" button click
      dialog.find("#cancel-room").on('mouseenter mouseleave', function(){
        $(this).toggleClass('btn-dark btn-danger');
      }).on('click', function() {
        dialog.modal("hide");
        dialog.on('hidden.bs.modal', function (event) {
          if (offset < {{ env('ROOM_OFFSET') }}) {
            showLatestRoom(offset + 1, data.room.code);
          } else if (offset == {{ env('ROOM_OFFSET') }} && !window.location.href.toLowerCase().includes('sanh-cho')) {
            bootbox.confirm({
              message: "Vào sảnh chờ!",
              size: 'small',
              locale: 'vi',
              centerVertical: true,
              closeButton: false,
              buttons: {
                confirm: {
                  label: '<i class="fas fa-check"></i> Vào',
                  className: 'btn-lg btn-danger pulse-red'
                },
                cancel: {
                  className: 'btn-lg btn-dark text-light'
                }
              },
              callback: function (result) {
                if (result == true) {
                  // $('#AdSenseModal').attr('data-url', "{{ url('/sanh-cho') }}").modal('show');
                  // $('#AdSenseModal').on('shown.bs.modal', function (event) {
                  //   $('#adModalCloseBtn').attr('data-original-title', 'Mời vào Sảnh chờ!');
                  //   $('#adModalCloseBtn').tooltip();
                  // });
                  window.location.href = "{{ url('/sanh-cho') }}";
                }
              }
            });
          }
        })
      });
    }
  });
}
// window.onscroll = function() {
//   if (window.innerHeight + window.pageYOffset >= (document.body.offsetHeight / 3)) {
//     showLatestRoom(0);
//     window.onscroll = null;
//   }
// }
$(function () {
  $('.menu-toggle').on('click', function(){
    $(this).toggleClass('open close');
  });
  let activeNavLinkSelectors = 'body.dashboard nav ul.navbar-nav li a.dashboard, body.login nav ul.navbar-nav li a.login, body.register nav ul.navbar-nav li a.register';
  $(activeNavLinkSelectors).each(function() {
    $(this).find('i').removeClass('far').addClass('fas');
  });
  $('nav ul.navbar-nav').on('mouseenter mouseleave', function() {
    $(activeNavLinkSelectors).each(function() {
      $(this).find('i').toggleClass('far fas');
    });
  });
  $('nav ul.navbar-nav li a').each(function() {
    $(this).on('mouseenter mouseleave', function() {
      $(this).find('i').toggleClass('far fas');
    });
  });
  $('.btn').each(function(){
    $(this).on('mouseenter mouseleave', function(){
      $(this).find('i:not(.fab)').toggleClass('fad fas');
    });
  });
  $('.btn-dark').each(function(){
    $(this).on('mouseenter mouseleave', function(){
      $(this).toggleClass('btn-dark btn-danger');
    });
  });
});
</script>
<script>
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
$('#tourBtn').on('click', function(){
  introJs().setOptions({"nextLabel": "Sau", "prevLabel": "Trước", "skipLabel": "Bỏ qua", "doneLabel": "Hoàn tất", "showProgress": true, "showButtons": true, "showBullets": true, "exitOnOverlayClick": true, "hidePrev": true, "hideNext": true, "disableInteraction": true}).onskip(function(){
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }).onexit(function(){
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }).start();
});
</script>
{{-- <script src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-62e5116d104f368b"></script> --}}
<script src='https://platform-api.sharethis.com/js/sharethis.js#property=646aee4bd8c6d2001a06c2f8&product=sticky-share-buttons' async='async'></script>
<a href="#0" class="cd-top js-cd-top rounded" style="background-image: url('{{ url('/') }}/img/cd-top-arrow.svg');">Top</a>
<script src="{{ url('/') }}/js/to-top.js"></script>