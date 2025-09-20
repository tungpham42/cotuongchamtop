<footer>
  <div class="container">
    <div class="row p-5">
      <div class="col-12 col-xl-3 col-lg-3 col-md-6 col-sm-12 mb-3 vcard">
        <p>Contact email</p>
        <a class="w-100 email" href="mailto:tung.42@gmail.com">tung.42@gmail.com</a>
        <p class="mt-3"><i class="fal fa-copyright"></i> Copyright <a class="url fn h-card" target="_blank" href="https://tungpham42.info/">Tung Pham</a></p>
      </div>
      <div class="col-12 col-xl-3 col-lg-3 col-md-6 col-sm-12 mb-3">
        <ul class="list-unstyled">
          <li>
            <a class="home" href="{{ url('/amp/en') }}">Home</a>
          </li>
          <li>
            <a class="room" href="{{ url('/amp/rooms') }}">Rooms</a>
          </li>
          <li>
            <a class="setup" href="{{ url('/amp/set-up') }}">Set Up</a>
          </li>
          <li>
            <a class="about" href="{{ url('/amp/about-us') }}">About Us</a>
          </li>
          <li>
            <a class="contact" href="{{ url('/amp/contact-us') }}">Contact Us</a>
          </li>
          <li>
            <a class="lang" href="{{ $langUrl }}">Tiếng Việt</a>
          </li>
          <li>
            <a target="_blank" class="buy" href="https://www.codester.com/items/24523/dual-languages-xiangqi-game-with-ai-and-room-host?ref=tungpham"><i class="fad fa-shopping-cart"></i> BUY</a>
          </li>
        </ul>
      </div>
      <div class="col-12 col-xl-3 col-lg-3 col-md-6 col-sm-12 mb-3">
        <p>Find us on social media</p>
        <a class="w-100 mr-2 display-4" target="_blank" href="https://www.facebook.com/CoTuongPage/"><i class="fab fa-facebook-square rounded"></i></a>
        <a class="w-100 mr-2 display-4" target="_blank" href="https://www.linkedin.com/company/cotuong/"><i class="fab fa-linkedin rounded"></i></a>
      </div>
      <div class="col-12 col-xl-3 col-lg-3 col-md-6 col-sm-12 mb-3">
        <p>Verified with HTML5</p>
        <a title="Valid HTML 5" class="w-100 mr-2 display-4" target="_blank" href="https://validator.w3.org/check?uri=referer">
          <i class="fab fa-html5"></i>
        </a>
      </div>
    </div>
  </div>
</footer>
<div class="modal fade" id="AdBlockModal" tabindex="-1" role="dialog" aria-label="AdBlock" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title">AdBlock detected</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Sure, ad-blocking software does a great job at blocking ads, but it also blocks some useful and important features of our website. For the best possible site experience please take a moment to disable your AdBlocker.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="AdSenseModal" tabindex="-1" role="dialog" aria-label="AdSense" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title">Ads</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
        <!-- CO_300x300 -->
        <ins class="adsbygoogle"
            style="display:inline-block;width:300px;height:300px"
            data-ad-client="ca-pub-3585118770961536"
            data-ad-slot="7625346255"></ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
$('.menu-toggle').on('click', function(){
  if ($(this).hasClass('open')) {
    $(this).removeClass('open').removeClass('close').addClass('close');
  } else if ($(this).hasClass('close')) {
    $(this).removeClass('close').removeClass('open').addClass('open');
  }
});
// Function called if AdBlock is not detected
function adBlockNotDetected() {
//  alert('AdBlock is not enabled');
}
// Function called if AdBlock is detected
function adBlockDetected() {
  $('#AdBlockModal').modal();
}
$('#AdSenseModal').on('hide.bs.modal', function(e) {
  window.location.href = $('#tao-phong').attr('data-url');
})
justDetectAdblock.detectAnyAdblocker().then(function(detected) {
  if(detected){
    adBlockDetected();
  }
});
</script>
<a href="#0" class="cd-top js-cd-top rounded" style="background-image: url('https://cotuong.r.worldssl.net/img/cd-top-arrow.svg');">Top</a>
<script src="{{ URL::to('/') }}/js/to-top.js"></script>