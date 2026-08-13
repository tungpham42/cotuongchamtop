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
<a href="#0" class="cd-top js-cd-top rounded" style="background-image: url('{{ asset('img/cd-top-arrow.svg') }}');">Top</a>
<script src="{{ asset('js/to-top.js') }}"></script>
@include('cookie-consent::index')
