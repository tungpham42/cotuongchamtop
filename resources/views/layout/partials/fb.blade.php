<div style="background-color: #312e2b" class="container-fluid comments px-0">
  <div class="container mx-auto px-0">
    <div class="row w-100 mx-auto p-3">
      <h2 class="mb-4 w-100 text-light"><i class="fas fa-comments"></i> Bình luận</h2>
      <div id="fb-root"></div>
      <script>
      $(document).ready(function() {
        $.ajax({
          url: 'https://connect.facebook.net/vi_VN/sdk.js',
          type: 'GET',
          cache: true,
          global: false,
          dataType: 'script',
          async: true
        }).done(function(){
          FB.init({
            appId: '279071963296709',
            cookie : true,
            xfbml : true,
            version : 'v19.0'
          });
        });
      });
      </script>
      <div id="fb-customer-chat" class="fb-customerchat"></div>
      <div class="fb-login-button facebook_plugin" data-width="300" data-max-rows="1" data-size="medium" data-show-faces="true" data-auto-logout-link="true"></div>
      <div id="fb_like" class="fb-like facebook_plugin" data-width="300" data-href="" data-layout="standard" data-action="like" data-show-faces="true" data-share="true"></div>
      <div id="fb_comments" class="fb-comments" data-href="" data-lazy="true" data-width="100%" data-numposts="12" data-colorscheme="dark" data-order-by="reverse_time"></div>
      <script>
      var chatbox = document.getElementById('fb-customer-chat');
      chatbox.setAttribute("page_id", "101790641431853");
      chatbox.setAttribute("attribution", "biz_inbox");
      chatbox.setAttribute("logged_in_greeting", "Xin chào kỳ thủ!");
      chatbox.setAttribute("logged_out_greeting", "Chúng tôi giúp được gì cho quý vị ạ?");
      chatbox.setAttribute("greeting_dialog_display", "show");
      chatbox.setAttribute("greeting_dialog_delay", "2");
      chatbox.setAttribute("theme_color", "#f04124");
      (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = 'https://connect.facebook.net/vi_VN/sdk/xfbml.customerchat.js';
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));
@if ($roomCode != '')
      $('#fb_like, #fb_comments').attr('data-href', '{{ url("/phong/{$roomCode}") }}');
@else
      $('#fb_like, #fb_comments').attr('data-href', '{{ url()->current() }}');
@endif
      </script>
    </div>
  </div>
</div>