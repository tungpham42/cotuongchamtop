<!-- @include('common.scripts') -->
@desktop
<script src="{{ $cdnUrl }}/js/xiangqiboard.js?v=32"></script>
@elsedesktop
<script src="{{ $cdnUrl }}/js/xiangqiboard_mobile.js?v=2"></script>
@enddesktop
<script>
$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});
var locale = {
  OK: '<i class="fas fa-check"></i> 알았어요',
  CONFIRM: '<i class="fas fa-check"></i> 확인',
  CANCEL: '<i class="fas fa-times"></i> 취소하다'
};

bootbox.addLocale('ko', locale);
$('#tao-phong-public').on('click auxclick', function(e) {
  e.preventDefault();
  bootbox.prompt({
    title: "새 방의 이름을 만드십시오:",
    locale: 'ko',
    centerVertical: true,
    closeButton: false,
    maxlength: 32,
    buttons: {
      confirm: {
        label: '<i class="fas fa-check"></i> 좋아요',
        className: 'btn-lg btn-danger pulse-red'
      },
      cancel: {
        className: 'btn-lg btn-dark text-light'
      }
    },
    callback: function(roomName){
      if (roomName != null) {
        if (roomName.trim().length === 0 || roomName.length === 0) {
          bootbox.alert({
            message: "이름을 입력하세요.",
            size: 'small',
            locale: 'ko',
            centerVertical: true,
            closeButton: false,
            buttons: {
              ok: {
                className: 'btn-lg btn-danger pulse-red'
              }
            },
            callback: function () {
              $('#tao-phong-public').trigger('click');
            }
          });
        } else {
          $.ajax({
            type: "POST",
            url: '{{ url('/api') }}/createRoom',
            data: {
              'ma-phong': $('#tao-phong').attr('data-phong'),
              'ten-phong': roomName,
              'FEN': 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1',
              'pass': ''
            },
            dataType: 'text'
          }).done(function() {
            $('#AdSenseModal').attr('data-url', $('#tao-phong').attr('data-url')).modal('show');
            $('#adModalCloseBtn').attr('data-original-title', $('#AdSenseModal').attr('data-url'));
            $('#adModalCloseBtn').tooltip();
          });
        }
      }
    }
  });
});
$('#tao-phong-private').on('click auxclick', function(e) {
  e.preventDefault();
  bootbox.prompt({
    title: "새 방의 이름을 만드십시오:",
    locale: 'ko',
    centerVertical: true,
    closeButton: false,
    maxlength: 32,
    buttons: {
      confirm: {
        label: '<i class="fas fa-check"></i> 좋아요',
        className: 'btn-lg btn-danger pulse-red'
      },
      cancel: {
        className: 'btn-lg btn-dark text-light'
      }
    },
    callback: function(roomName){
      if (roomName != null) {
        if (roomName.trim().length === 0 || roomName.length === 0) {
          bootbox.alert({
            message: "이름을 입력하세요.",
            size: 'small',
            locale: 'ko',
            centerVertical: true,
            closeButton: false,
            buttons: {
              ok: {
                className: 'btn-lg btn-danger pulse-red'
              }
            },
            callback: function () {
              $('#tao-phong-private').trigger('click');
            }
          });
        } else {
          bootbox.prompt({
            title: "새 방의 비밀번호를 만드세요!",
            locale: 'ko',
            centerVertical: true,
            closeButton: false,
            buttons: {
              confirm: {
                label: '<i class="fas fa-check"></i> 만들다',
                className: 'btn-lg btn-danger pulse-red'
              },
              cancel: {
                className: 'btn-lg btn-dark text-light'
              }
            },
            callback: function(password){
              console.log('Mật khẩu là: ' + password);
              if (password != null) {
                if (password.trim().length === 0 || password.length === 0) {
                  bootbox.alert({
                    message: "비밀번호를 입력하세요. 그런 다음 친구에게 보내십시오.",
                    size: 'small',
                    locale: 'ko',
                    centerVertical: true,
                    closeButton: false,
                    buttons: {
                      ok: {
                        className: 'btn-lg btn-danger pulse-red'
                      }
                    },
                    callback: function () {
                      $('#tao-phong-private').trigger('click');
                    }
                  });
                } else {
                  $.ajax({
                    type: "POST",
                    url: '{{ url('/api') }}/createRoom',
                    data: {
                      'ma-phong': $('#tao-phong').attr('data-phong'),
                      'ten-phong': roomName,
                      'FEN': 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1',
                      'pass': password
                    },
                    dataType: 'text'
                  }).done(function() {
                    $('#AdSenseModal').attr('data-url', $('#tao-phong').attr('data-url')).modal('show');
                    $('#adModalCloseBtn').attr('data-original-title', $('#AdSenseModal').attr('data-url'));
                    $('#adModalCloseBtn').tooltip();
                  });
                }
              }
            }
          });
        }
      }
    }
  });
});
@if ($roomCode == '')
function showLatestRoom(offset, newCode) {
  $.ajax({
    type: "POST",
    url: '{{ url('/api') }}/getLatestRoom',
    data: {
      'offset': offset
    },
    dataType: "json"
  }).done(function(data){
    if (data.room.code != '{{ $roomCode }}' && data.room.code != newCode) {
      var htmlContent = `
        <button id="join-room" class="btn btn-lg btn-danger float-right ml-2"><i class="fas fa-sign-in-alt"></i> 지금 재생</button>
        <button id="cancel-room" class="btn btn-lg btn-dark float-right"><i class="fas fa-times"></i> 취소하다</button>
      `;
      var dialog = bootbox.dialog({
        title: '"' + data.room.name + '"방이 새로 생성되었습니다!',
        message: htmlContent,
        locale: 'ko',
        size: 'small',
        centerVertical: true,
        closeButton: false
      });
      dialog.find("#join-room").on('click', function() {
        if (data.color == 'red') {
          dialog.modal("hide");
          $('#AdSenseModal').attr('data-url', "{{ url('/') }}" + '/bang/' + data.room.code + '/ppalgan').modal('show');
          $('#AdSenseModal').on('shown.bs.modal', function (event) {
            $('#adModalCloseBtn').attr('data-original-title', '빨간색이 다음에 "' + data.room.name + '" 방에 갑니다.');
            $('#adModalCloseBtn').tooltip();
          })
          // window.location.href = "{{ url('/') }}" + '/bang/' + data.room.code;
        } else if (data.color == 'black') {
          dialog.modal("hide");
          $('#AdSenseModal').attr('data-url', "{{ url('/') }}" + '/bang/' + data.room.code + '/geom-eunsaeg').modal('show');
          $('#AdSenseModal').on('shown.bs.modal', function (event) {
            $('#adModalCloseBtn').attr('data-original-title', '검정색이 다음에 "' + data.room.name + '" 방에 갑니다.');
            $('#adModalCloseBtn').tooltip();
          })
          // window.location.href = "{{ url('/') }}" + '/bang/' + data.room.code + '/bangmun';
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
          } else if (offset == {{ env('ROOM_OFFSET') }} && !window.location.href.toLowerCase().includes('bang-moglog')) {
            bootbox.confirm({
              message: "방 목록으로 이동하세요!",
              size: 'small',
              locale: 'ko',
              centerVertical: true,
              closeButton: false,
              buttons: {
                confirm: {
                  className: 'btn-lg btn-danger pulse-red rooms-list'
                },
                cancel: {
                  className: 'btn-lg btn-dark text-light',
                  id: 'cancel-room'
                }
              },
              callback: function (result) {
                if (result == true) {
                  $('#AdSenseModal').attr('data-url', "{{ url('/bang-moglog') }}").modal('show');
                  $('#AdSenseModal').on('shown.bs.modal', function (event) {
                    $('#adModalCloseBtn').attr('data-original-title', '방 목록으로 가요!');
                    $('#adModalCloseBtn').tooltip();
                  });
                  // window.location.href = "{{ url('/bang-moglog') }}";
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
//     showLatestRoom(0, '{{ $roomCode }}');
//     window.onscroll = null;
//   }
// }
@endif
$('#random-room').on('click auxclick', function(e) {
  e.preventDefault();
  $('#AdSenseModal').attr('data-url', $(this).attr('href')).modal('show');
});
$('#room-list').on('click auxclick', function(e) {
  e.preventDefault();
  $('#AdSenseModal').attr('data-url', $(this).attr('href')).modal('show');
});
$('#copy-url-red').on('click', function() {
  copyToClipboard('#url-red');
  selectText('#url-red');
  $(this).tooltip('update');
});
$('#copy-url-black').on('click', function() {
  copyToClipboard('#url-black');
  selectText('#url-black');
  $(this).tooltip('update');
});
$('#room-code').on('click', function() {
  copyToClipboard('#room-code-input');
  selectText('#room-code-input');
  $(this).find('span').tooltip('update');
});
const nuocCo = document.getElementById("nuoc-co");
const hetTran = document.getElementById("het-tran");

$(function () {
  $('.dropdown-toggle').dropdown();
  if (!Modernizr.touch) {
    $('#volumeSwitch').attr('title', '볼륨을 켜기/끄기하려면 여기를 클릭하세요');
    $('#tourBtn').attr('title', '웹사이트 투어를 시작하려면 여기를 클릭하세요');
    $('[data-toggle="tooltip"]').tooltip();
    document.addEventListener('contextmenu', function(e) {
      e.preventDefault();
    });
  }
  $('.dropdown-item').each(function() {
    $(this).on('mouseenter mouseleave', function() {
      $(this).find('i').toggleClass('fad fas');
    });
  });
  let activeNavLinkSelectors = 'body.home header.site-header a.home, body.setup header.site-header a.setup, body.about header.site-header a.about, body.bmi header.site-header a.bmi, body.game header.site-header a.game, body.room header.site-header a.room, body.news header.site-header a.news, body.contact header.site-header a.contact';
  $(activeNavLinkSelectors).each(function() {
    $(this).find('i').removeClass('far').addClass('fas');
  });
  $('header.site-header ul.navbar-nav').on('mouseenter mouseleave', function() {
    $(activeNavLinkSelectors).each(function() {
      $(this).find('i').toggleClass('far fas');
    });
  });
  $('header.site-header li > a').each(function() {
    $(this).on('mouseenter mouseleave', function() {
      $(this).find('i').toggleClass('far fas');
    });
  });
});
$('#tourBtn').on('click', function(){
  introJs().setOptions({"nextLabel": "다음", "prevLabel": "이전", "skipLabel": "건너뛰기", "doneLabel": "끝났다", "showProgress": true, "showButtons": true, "showBullets": true, "exitOnOverlayClick": true, "hidePrev": true, "hideNext": true, "disableInteraction": true}).onskip(function(){
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }).onexit(function(){
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }).start();
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
window.onload = () => {
  'use strict';
  if ('serviceWorker' in navigator) {
    console.log("Will the service worker register?");
    navigator.serviceWorker
    .register('{{ URL::to('/') }}/serviceWorker.js')
    .then(function(reg) {
      console.log("Yes, it did.");
    }).catch(function(err) {
      console.log("No it didn't. This happened:", err)
    });
  }
}
</script>
<!-- Go to www.addthis.com/dashboard to customize your tools -->
<!-- <script src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-62e5116d104f368b"></script> -->
<script src='https://platform-api.sharethis.com/js/sharethis.js#property=646aee4bd8c6d2001a06c2f8&product=sticky-share-buttons' async='async'></script>