<div class="container-fluid comments px-0 border-top">
  <div class="container mx-auto px-0">
    <div class="row w-100 mx-auto p-3">
      <h2 class="mb-4 w-100">Bình luận</h2>
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
            version : 'v7.0'
          });
        });
      });
      </script>
      <div class="fb-login-button facebook_plugin" data-width="300" data-max-rows="1" data-size="medium" data-show-faces="true" data-auto-logout-link="true"></div>
      <div id="fb_like" class="fb-like facebook_plugin" data-width="300" data-href="" data-layout="standard" data-action="like" data-show-faces="true" data-share="true"></div>
      <div id="fb_comments" class="fb-comments" data-href="" data-width="100%" data-numposts="12" data-colorscheme="light"></div>
      <script>
@if ($roomCode != '')
      $('#fb_like, #fb_comments').attr('data-href', '{{ url("/phong/{$roomCode}") }}');
@else
      $('#fb_like, #fb_comments').attr('data-href', '{{ url()->current() }}');
@endif
      </script>
    </div>
  </div>
</div>