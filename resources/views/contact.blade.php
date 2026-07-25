@extends('layout.mainlayout')

@section('aboveContent')
<div class="container-fluid contact px-0">
  <div class="container p-3">
    <h2 class="h1-responsivefooter text-center my-4">{{ __("Liên hệ") }}</h2>
    @include('common.map')
    <div class="row">
      <div class="col-md-9 mb-md-0 mb-5">
        <form id="contact-form" name="contact-form" action="" method="POST">
          @csrf
          <input type="hidden" name="lang" value="{{ app()->getLocale() }}">
          <div class="row">
            <div class="col-md-6">
              <div class="md-form mb-0">
                <input type="text" id="name" name="name" class="form-control">
                <label for="name">{{ __("Họ tên") }}</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="md-form mb-0">
                <input type="text" id="email" name="email" class="form-control">
                <label for="email">Email</label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="md-form mb-0">
                <input type="text" id="subject" name="subject" class="form-control">
                <label for="subject">{{ __("Chủ đề") }}</label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="md-form">
                <textarea id="message" name="message" rows="8" class="form-control md-textarea"></textarea>
                <label for="message">{{ __("Tin nhắn") }}</label>
              </div>
            </div>
          </div>
        </form>

        <div class="text-center text-md-left">
          <a class="btn btn-danger btn-lg" onclick="validateForm();">{{ __("Gửi") }}</a>
        </div>
        <div id="status" class="mt-3 font-weight-bold"></div>
      </div>

      <div class="col-md-3 text-center">
        <ul class="list-unstyled mb-0">
          <li><i class="fas fa-map-marker-alt fa-2x"></i>
            <p>{{ __("TP. Hồ Chí Minh, 756000,") }} <br/>Việt Nam</p>
          </li>
          <li><i class="fas fa-phone mt-4 fa-2x"></i>
            <p>+ 84 368 571 310</p>
          </li>
          <li><i class="fas fa-envelope mt-4 fa-2x"></i>
            <p><a class="text-light" href="mailto:tung.42@gmail.com">tung.42@gmail.com</a></p>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>
@endsection

@section('belowContent')
<script>
function validateForm() {
  $('#status').text("{{ __('Đang xử lý') }}...");

  $.ajax({
    url: "{{ route('mail.contact') }}",
    type: "POST",
    data: $('#contact-form').serialize(),
    dataType: 'json',
    success: function(data) {
      $('#status').text(data.message);
      if (data.code) {
        $('#contact-form')[0].reset();
      }
    },
    error: function (jqXHR) {
      $('#status').text("Error: " + jqXHR.statusText);
    }
  });
}
</script>
@endsection
