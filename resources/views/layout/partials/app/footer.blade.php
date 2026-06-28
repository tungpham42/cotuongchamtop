<footer>
  <div class="container">
    @include('common.adsenseBottom')
    <div class="row py-5 px-0">
      <div class="col-12 col-xl-3 col-lg-3 col-md-6 col-sm-12 mb-3 vcard">
        <p>{{ __("Email liên hệ") }}</p>
        <a class="w-100 email showPromotion" href="mailto:tung.42@gmail.com">tung.42@gmail.com</a>
        <p class="mt-3">{{ __("Bản quyền") }} <i class="fal fa-copyright"></i> {{ date('Y') }} <a class="url fn h-card showPromotion" target="_blank" href="https://tungpham42.github.io/">Phạm Tùng</a></p>
        <div class="bg-white p-1" style="width: fit-content; border-radius: 0.5rem;"><a href="https://soft.io.vn" target="_blank"><img alt="Logo Soft" height="40" class="me-2" src="{{ asset('img/soft-logo.webp') }}"></a></div>
      </div>
      <div class="col-12 col-xl-3 col-lg-3 col-md-6 col-sm-12 mb-3">
        <ul class="list-unstyled">
          <li>
            <a class="home showPromotion" href="{{ localized_url('home') }}"><i class="fal fa-home-lg-alt"></i> {{ __("Trang chủ") }}</a>
          </li>
          <li>
            <a class="dashboard showPromotion" href="{{ localized_url('app.dashboard') }}"><i class="fal fa-trophy-alt"></i> {{ __("Thi đấu") }}</a>
          </li>
          <li>
            <a class="trophy showPromotion" href="{{ localized_url('app.ranking') }}"><i class="fal fa-star"></i> {{ __("Bảng xếp hạng") }}</a>
          </li>
          <li>
            <a class="room showPromotion rooms-list" href="{{ localized_url('room.list') }}"><i class="fal fa-list-alt"></i> {{ __("Sảnh chờ") }}</a>
          </li>
          <li>
            <a class="setup puzzle showPromotion" href="{{ localized_url('puzzle.setup') }}"><i class="fal fa-puzzle-piece"></i> {{ __("Cờ thế") }}</a>
          </li>
          <li>
            <a class="about showPromotion" href="{{ localized_url('about') }}"><i class="fal fa-info-square"></i> {{ __("Giới thiệu") }}</a>
          </li>
          <li>
            <a class="contact showPromotion" href="{{ localized_url('contact') }}"><i class="fal fa-envelope"></i> {{ __("Liên hệ") }}</a>
          </li>
          <li>
            <a target="_blank" class="game showPromotion" href="https://game.cotuong.top"><i class="fal fa-gamepad-alt"></i> {{ __("Trò chơi") }}</a>
          </li>
          <li>
            <a target="_blank" class="buy showPromotion" href="https://www.codester.com/items/41601/multilingual-chinese-chess-game-with-many-options?ref=tungpham"><i class="fal fa-shopping-cart"></i> {{ __("Mua mã nguồn") }}</a>
          </li>
          <li>
            <a target="_blank" class="hikari showPromotion" href="https://hikarilearn.io.vn/"><i class="fal fa-book-reader"></i> {{ __("Học tiếng Nhật") }}</a>
          </li>
          <li>
            <a target="_blank" class="hololab showPromotion" href="https://hololab.vn/"><i class="fal fa-cube"></i> Hologram</a>
          </li>
        </ul>
      </div>
      <div class="col-12 col-xl-3 col-lg-3 col-md-6 col-sm-12 mb-3">
        <p>{{ __("Chúng tôi trên mạng xã hội") }}</p>
        <a class="w-100 mr-2 display-4 showPromotion" target="_blank" href="https://www.youtube.com/@CoTuongVlog/videos"><i class="fab fa-youtube"></i></a>
        <a class="w-100 mr-2 display-4 showPromotion" target="_blank" href="https://www.facebook.com/CoTuongPage/"><i class="fab fa-facebook-square rounded"></i></a>
        <a class="w-100 mr-2 display-4 showPromotion" target="_blank" href="https://www.linkedin.com/company/cotuong/"><i class="fab fa-linkedin rounded"></i></a>
      </div>
      <div class="col-12 col-xl-3 col-lg-3 col-md-6 col-sm-12 mb-3">
        <p>{{ __("Đã xác thực HTML5 và CSS3") }}</p>
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
  OK: '<i class="fas fa-check"></i> {{ __("Đồng ý") }}',
  CONFIRM: '<i class="fas fa-check"></i> {{ __("Chấp nhận") }}',
  CANCEL: '<i class="fas fa-times"></i> {{ __("Hủy") }}'
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
        <button id="join-room" class="btn btn-lg btn-danger float-right ml-2"><i class="fas fa-sign-in-alt"></i> {{ __("Vào") }}</button>
        <button id="cancel-room" class="btn btn-lg btn-dark float-right"><i class="fas fa-times"></i> {{ __("Hủy") }}</button>
      `;
      var dialog = bootbox.dialog({
        title: '{{ __("Bạn được thách đấu tại") }} "' + data.room.name + '"!',
        message: htmlContent,
        locale: '{{ __("vi") }}',
        size: 'small',
        centerVertical: true,
        closeButton: false
      });
      dialog.find("#join-room").on('click', function() {
        if (data.color == 'red') {
          dialog.modal("hide");
          let url = '{{ localized_url("room.red", ["code" => ":code"]) }}';
          window.location.href = url.replace(/%3Acode|:code/g, data.room.code);
        } else if (data.color == 'black') {
          dialog.modal("hide");
          let url = '{{ localized_url("room.black", ["code" => ":code"]) }}';
          window.location.href = url.replace(/%3Acode|:code/g, data.room.code);
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
              message: '{{ __("Vào sảnh chờ!") }}',
              size: 'small',
              locale: '{{ __("vi") }}',
              centerVertical: true,
              closeButton: false,
              buttons: {
                confirm: {
                  label: '<i class="fas fa-check"></i> {{ __("Vào") }}',
                  className: 'btn-lg btn-danger pulse-red'
                },
                cancel: {
                  className: 'btn-lg btn-dark text-light'
                }
              },
              callback: function (result) {
                if (result == true) {
                  window.location.href = "{{ localized_url('room.list') }}";
                }
              }
            });
          }
        })
      });
    }
  });
}

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
  $(this).on('click auxclick', function(e){
      window.location.href = $(this).attr('href');
  });
});
$('#AdSenseModal').on('show.bs.modal', function(){
  if (!$('#AdSenseModal').find('ins').attr('data-ad-status')) {
    $('#AdSenseModal').find('ins').attr('data-ad-status', 'unfilled');
  }
}).on('shown.bs.modal', function() {
  $('#adModalCloseBtn').attr('data-original-title', '{{ __("Đi đến:") }} ' + $(this).attr('data-url')).css('cursor', 'wait').prop('disabled', true);
  $('#adModalCloseBtn').tooltip();
  let i = 2;
  let timer = setInterval(function() {
    console.log(--i);
    $('#adModalCloseBtn').find('span').text(i + ' {{ __("giây") }}');
    if (i === -1) {
      $('#adModalCloseBtn').find('i').removeClass('fa-clock').addClass('fa-link');
      $('#adModalCloseBtn').css('cursor', 'pointer').removeClass('disabled').removeAttr('disabled').addClass('pulse-red').find('span').text('{{ __("Đến ngay") }}');
      clearInterval(timer);
    }
  }, 1000);
}).on('hidden.bs.modal', function() {
  $('#adModalCloseBtn').find('span').text('2 {{ __("giây") }}');
  window.location.href = $(this).attr('data-url');
});
$('#tourBtn').on('click', function(){
  introJs().setOptions({"nextLabel": "{{ __("Sau") }}", "prevLabel": "{{ __("Trước") }}", "skipLabel": "{{ __("Bỏ qua") }}", "doneLabel": "{{ __("Hoàn tất") }}", "showProgress": true, "showButtons": true, "showBullets": true, "exitOnOverlayClick": true, "hidePrev": true, "hideNext": true, "disableInteraction": true}).onskip(function(){
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }).onexit(function(){
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }).start();
});

$(document).ready(function() {
    // 1. Quét qua các cột có khả năng bị cắt chữ
    $('.room-code span, .host-name, .guest-name').each(function() {
        // Lấy văn bản thuần (bỏ các thẻ HTML như <a>, <i>)
        var pureText = $(this).text().trim();

        // 2. Gắn attribute cho Bootstrap Tooltip
        if (pureText.length > 0) {
            $(this).attr('data-toggle', 'tooltip');
            $(this).attr('data-placement', 'top'); // Có thể đổi thành 'bottom' nếu muốn
            $(this).attr('title', pureText);

            // Fix nhẹ để thẻ <td> hiển thị con trỏ chuột dạng help/pointer khi hover
            $(this).css('cursor', 'pointer');
        }
    });

    // 3. Khởi tạo Bootstrap Tooltip jQuery Plugin
    // Nếu bạn dùng Bootstrap 5, cú pháp có thể là $('[data-bs-toggle="tooltip"]')
    $('[data-toggle="tooltip"]').tooltip({
        trigger: 'hover',       // Chỉ hiện khi hover
        animation: true,        // Bật hiệu ứng fade
        delay: { "show": 300, "hide": 100 } // Độ trễ xuất hiện (giúp đỡ rối mắt khi lướt chuột nhanh)
    });
});
</script>
<script src='https://platform-api.sharethis.com/js/sharethis.js#property=646aee4bd8c6d2001a06c2f8&product=sticky-share-buttons' async='async'></script>
<a href="#0" class="cd-top js-cd-top rounded" style="background-image: url('{{ asset('img/cd-top-arrow.svg') }}');">{{ __("Top") }}</a>
<script src="{{ asset('js/to-top.js') }}"></script>
