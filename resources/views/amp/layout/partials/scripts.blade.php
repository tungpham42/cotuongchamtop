<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.bundle.min.js" integrity="sha512-wV7Yj1alIZDqZFCUQJy85VN+qvEIly93fIQAN7iqDFCPEucLCeNFz4r35FCo9s6WrpdDQPi80xbljXB8Bjtvcg==" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/notify/0.4.2/notify.min.js" integrity="sha256-tSRROoGfGWTveRpDHFiWVz+UXt+xKNe90wwGn25lpw8=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js" integrity="sha256-0rguYS0qgS6L4qVzANq4kjxPLtvnp5nn2nB5G1lWRv4=" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.23/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.2/bootbox.min.js" integrity="sha512-RdSPYh1WA6BF0RhpisYJVYkOyTzK4HwofJ3Q7ivt/jkpW6Vc8AurL1R+4AUcvn9IwEKAPm/fk7qFZW3OuiUDeg==" crossorigin="anonymous"></script>
<script src="https://cotuong.r.worldssl.net/js/detect_adblock.js"></script>
<script src="https://cotuong.r.worldssl.net/js/scripts.js"></script>
<script src="https://cotuong.r.worldssl.net/js/manipulation.js"></script>
<script src="https://cotuong.r.worldssl.net/js/xiangqiboard.js?v=25"></script>
<script src="https://cotuong.r.worldssl.net/js/xiangqi.js?v=33"></script>
<script>
$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});
var locale = {
  OK: 'Đồng ý',
  CONFIRM: 'Chấp nhận',
  CANCEL: 'Hủy'
};
bootbox.addLocale('vi', locale);

$('#tao-phong').on('click', function() {
  bootbox.prompt({
    title: "Mời tạo mật khẩu cho phòng:",
    required: true,
    locale: 'vi',
    centerVertical: true,
    buttons: {
      confirm: {
        className: 'btn-success'
      }
    },
    callback: function(password){
      $.ajax({
        type: "POST",
        url: '{{ url('/api') }}/createRoom',
        data: {
          'ma-phong': $('#tao-phong').attr('data-phong'),
          'FEN': 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1',
          'pass': password
        },
        dataType: 'text'
      }).done(function() {
        // $('#AdSenseModal').modal('show');
        window.location.href = $('#tao-phong').attr('data-url');
      });
    }
  });
});
$('#copy-url-red').on('click', function() {
  copyToClipboard('#url-red');
  selectText('#url-red')
});
$('#copy-url-black').on('click', function() {
  copyToClipboard('#url-black');
  selectText('#url-black')
});
const nuocCo = document.getElementById("nuoc-co");
const hetTran = document.getElementById("het-tran");

$(function () {
  if (!Modernizr.touch) {
    $('[data-toggle="tooltip"]').tooltip();
    document.addEventListener('contextmenu', function(e) {
      e.preventDefault();
    });
  }
});
document.addEventListener('touchstart touchend touchmove', function(event) {
  event.preventDefault();
});
document.oncontextmenu = function(e){
  stopEvent(e);
}
function stopEvent(event){
  if(event.preventDefault != undefined)
    event.preventDefault();
  if(event.stopPropagation != undefined)
    event.stopPropagation();
}
// var is_iOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
// if (is_iOS) {
//   document.addEventListener('touchstart touchend touchcancel touchmove', event => {
//     event.preventDefault();
//   }, {passive: false});
// }

// window.onload = () => {
//   'use strict';
//   if ('serviceWorker' in navigator) {
//     console.log("Will the service worker register?");
//     navigator.serviceWorker
//     .register('{{ URL::to('/') }}/serviceWorker.js')
//     .then(function(reg) {
//       console.log("Yes, it did.");
//     }).catch(function(err) {
//       console.log("No it didn't. This happened:", err)
//     });
//   }
// }
</script>
<!-- Go to www.addthis.com/dashboard to customize your tools -->
{{-- <script src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5e65ab6b19fb59d9"></script> --}}