@if (!str_contains(url()->current(), '/fangjian/'))
<div class="container">
  <div class="row">
    <a id="puzzlesBtn" href="javascript:goToPuzzles();" class="position-absolute float-left btn btn-danger btn-lg pulse-red" data-toggle="tooltip" data-placement="bottom" title='转到{{ numberToWordsZh($puzzles->total()) }}神奇的谜题'><i class="fas fa-chevron-double-down"></i></a>
  </div>
</div>
<script>
function goToPuzzles() {
  window.scrollTo({ top: document.getElementById('mi').offsetTop, behavior: 'smooth' });
} 
</script>
@endif