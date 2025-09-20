@extends('layout.mainlayout')
@section('aboveContent')
<div class="container-fluid about px-0">
  <div class="container p-5">
    <h2 class="h1-responsivefooter text-center my-4">Chỉ số khối cơ thể</h2>
    <div class="row">
      <div id="bmi_app" class="mx-auto w-100 shadow-lg rounded">
        <input id="bmi_lang" type="hidden" v-model="language" value="vi">
        <div class="input-group">
          <div class="input-group-prepend col-4 col-xl-3 col-lg-3 col-md-4 col-sm-4 px-0">
            <span class="input-group-text w-100">Cân nặng</span>
          </div>
          <input class="form-control col-4 col-xl-6 col-lg-6 col-md-5 col-sm-5" pattern="\d*" id="weight" type="number" min="25" max="300" step="1" placeholder="84" v-model="weight" required="required" >
          <div class="input-group-append col-4 col-xl-3 col-lg-3 col-md-3 col-sm-3 px-0">
            <span class="input-group-text w-100">KG</span>
          </div>
        </div>
        <div class="input-group">
          <div class="input-group-prepend col-4 col-xl-3 col-lg-3 col-md-4 col-sm-4 px-0">
            <span class="input-group-text w-100">Chiều cao</span>
          </div>
          <input class="form-control col-4 col-xl-6 col-lg-6 col-md-5 col-sm-5" pattern="\d*" id="height" type="number" min="100" max="300" step="1" placeholder="184" v-model="height" required="required" >
          <div class="input-group-append col-4 col-xl-3 col-lg-3 col-md-3 col-sm-3 px-0">
            <span class="input-group-text w-100">CM</span>
          </div>
        </div>
        <div class="input-group">
          <div class="input-group-prepend col-12 col-xl-3 col-lg-3 col-md-4 col-sm-12 px-0">
            <span class="input-group-text w-100">Giá trị BMI</span>
          </div>
          <input type="text" class="form-control col-12 col-xl-9 col-lg-9 col-md-8 col-sm-12" disabled value="{{ bmiValue }}">
        </div>
        <div class="input-group">
          <div class="input-group-prepend col-12 col-xl-3 col-lg-3 col-md-4 col-sm-12 px-0">
            <span class="input-group-text w-100">Giải thích</span>
          </div>
          <input type="text" class="form-control col-12 col-xl-9 col-lg-9 col-md-8 col-sm-12" disabled value="{{ bmiExplanation }}">
        </div>
        <div class="input-group">
          <div class="input-group-prepend col-12 col-xl-3 col-lg-3 col-md-4 col-sm-12 px-0">
            <span class="input-group-text w-100">Cân nặng lý tưởng</span>
          </div>
          <input type="text" class="form-control col-12 col-xl-9 col-lg-9 col-md-8 col-sm-12" disabled value="{{ idealWeight }}">
        </div>
        <div class="input-group">
          <div class="input-group-prepend col-12 col-xl-3 col-lg-3 col-md-4 col-sm-12 px-0">
            <span class="input-group-text w-100">Chiều cao lý tưởng</span>
          </div>
          <input type="text" class="form-control col-12 col-xl-9 col-lg-9 col-md-8 col-sm-12" disabled value="{{ idealHeight }}">
        </div>
        <div class="input-group">
          <div class="input-group-prepend col-12 col-xl-3 col-lg-3 col-md-4 col-sm-12 px-0">
            <span class="input-group-text w-100">Lời khuyên</span>
          </div>
          <input type="text" class="form-control col-12 col-xl-9 col-lg-9 col-md-8 col-sm-12" disabled value="{{ recommendation }}">
        </div>
      </div>
    </div>
  </div>
</div>
<div class="container-fluid bmi-explanation px-0">
  <div class="container p-5">
    <h2 class="h1-responsivefooter text-center my-4">Giải nghĩa</h2>
    <div class="row">
      <p>Chỉ số khối cơ thể, cũng gọi là chỉ số thể trọng- thường được biết đến với tên viết tắt BMI theo tên tiếng Anh Body Mass Index, là một cách nhận định cơ thể của một người là gầy hay béo bằng một chỉ số. Chỉ số này do nhà khoa học người Bỉ Adolphe Quetelet đưa ra năm 1832.</p>
      <p>Chỉ số khối cơ thể của một người tính bằng trọng lượng (kg) chia cho bình phương chiều cao (đo theo mét hoặc cm). Con số này có thể tính theo công thức trên hoặc chiếu theo bảng tiêu chuẩn.</p>
      <p>Chỉ số này có thể giúp xác định một người bị béo phì hay bị suy dinh dưỡng một cách khoa học căn cứ trên số liệu về hình dáng, chiều cao và cân nặng cơ thể.</p>
    </div>
  </div>
</div>
@endsection
@section('belowContent')
<script src="https://cdn.jsdelivr.net/npm/vue"></script>
<script>
function roundTwoDecimal(number) {
	return Math.round(number*100)/100;
}
function calculateBmi(weight,height) {
	return roundTwoDecimal(weight/Math.pow(height/100,2));
}
function calculateWeight(bmi,height) {
	return Math.round(bmi*Math.pow(height/100,2));
}
function calculateHeight(bmi,weight) {
	return Math.round(Math.sqrt(weight/bmi)*100);
}
var app = new Vue({
  el: '#bmi_app',
  data: {
    bmiValue: calculateBmi(data.weight, data.height)
  }
})
</script>
@endsection