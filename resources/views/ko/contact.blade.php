@extends('ko.layout.mainlayout')
@section('aboveContent')
<div class="container-fluid contact px-0">
  <div class="container p-3">
    <h2 class="h1-responsivefooter text-center my-4">문의하기</h2>
    @include('common.map')
    <div class="row">
      <!--Grid column-->
      <div class="col-md-9 mb-md-0 mb-5">
        <form id="contact-form" name="contact-form" action="/mun-uihagi" method="POST">

          <!--Grid row-->
          <div class="row">

            <!--Grid column-->
            <div class="col-md-6">
              <div class="md-form mb-0">
                <input type="text" id="name" name="name" class="form-control">
                <label for="name" class="">이름</label>
              </div>
            </div>
            <!--Grid column-->

            <!--Grid column-->
            <div class="col-md-6">
              <div class="md-form mb-0">
                <input type="text" id="email" name="email" class="form-control">
                <label for="email" class="">이메일</label>
              </div>
            </div>
            <!--Grid column-->

          </div>
          <!--Grid row-->

          <!--Grid row-->
          <div class="row">
            <div class="col-md-12">
              <div class="md-form mb-0">
                <input type="text" id="subject" name="subject" class="form-control">
                <label for="subject" class="">주제</label>
              </div>
            </div>
          </div>
          <!--Grid row-->

          <!--Grid row-->
          <div class="row">

            <!--Grid column-->
            <div class="col-md-12">

              <div class="md-form">
                <textarea id="message" name="message" rows="8" class="form-control md-textarea"></textarea>
                <label for="message">메세지</label>
              </div>

            </div>
          </div>
          <!--Grid row-->

        </form>

        <div class="text-center text-md-left">
          <a class="btn btn-danger btn-lg" onclick="validateForm();">보내세요</a>
        </div>
        <div id="status"></div>
      </div>
      <!--Grid column-->

      <!--Grid column-->
      <div class="col-md-3 text-center">
        <ul class="list-unstyled mb-0">
          <li><i class="fas fa-map-marker-alt fa-2x"></i>
            <p>호찌민, 756000, <br/>베트남</p>
          </li>

          <li><i class="fas fa-phone mt-4 fa-2x"></i>
            <p>+ 84 368 571 310</p>
          </li>

          <li><i class="fas fa-envelope mt-4 fa-2x"></i>
            <p><a class="text-light" href="mailto:tung.42@gmail.com">tung.42@gmail.com</a></p>
          </li>
        </ul>
      </div>
      <!--Grid column-->
    </div>
  </div>
</div>
@endsection
@section('belowContent')
<script>
function validateForm() {
  document.getElementById('status').innerHTML = "처리...";
  formData = {
    'name'     : $('input[name=name]').val(),
    'email'    : $('input[name=email]').val(),
    'subject'  : $('input[name=subject]').val(),
    'message'  : $('textarea[name=message]').val()
  };
  $.ajax({
    url : "{{ url('/api') }}/processMailKo",
    type: "POST",
    data : formData,
    dataType: 'json',
    success: function(data, textStatus, jqXHR)
    {
      $('#status').text(data.message);
      console.log(data);
      if (data.code) //If mail was sent successfully, reset the form.
      $('#contact-form').closest('form').find("input[type=text], textarea").val("");
    },
    error: function (jqXHR, textStatus, errorThrown)
    {
      $('#status').text(jqXHR);
    }
  });
}
</script>
@endsection