@if (!str_contains(url()->current(), '/phong/'))
<div class="container">
  <div class="row">
    <a id="puzzlesBtn" href="javascript:goToPuzzles();" class="position-absolute float-left btn btn-danger btn-lg pulse-red" data-toggle="tooltip" data-placement="bottom" title='Xem {{ numberToWordsVi($userPuzzles->total()) }} thế cờ đặc sắc'><i class="fas fa-chevron-double-down"></i></a>
  </div>
</div>
<script>
function goToPuzzles() {
  window.scrollTo({ top: document.getElementById('the-co').offsetTop, behavior: 'smooth' });
} 
</script>
@endif