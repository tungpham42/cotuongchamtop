@if (!str_contains(url()->current(), '/bang/'))
<div class="container">
  <div class="row">
    <a id="puzzlesBtn" href="javascript:goToPuzzles();" class="position-absolute float-left btn btn-danger btn-lg pulse-red" data-toggle="tooltip" data-placement="bottom" title='{{ numberToWordsKo($puzzles->total()) }}놀라운 퍼즐 로 이동'><i class="fas fa-chevron-double-down"></i></a>
  </div>
</div>
<script>
function goToPuzzles() {
  window.scrollTo({ top: document.getElementById('peojeul').offsetTop, behavior: 'smooth' });
} 
</script>
@endif