@if (!str_contains(url()->current(), '/rumu/'))
<div class="container">
  <div class="row">
    <a id="puzzlesBtn" href="javascript:goToPuzzles();" class="position-absolute float-left btn btn-danger btn-lg pulse-red" data-toggle="tooltip" data-placement="bottom" title='{{ numberToWordsJa($puzzles->total()) }}すごいパズルへ'><i class="fas fa-chevron-double-down"></i></a>
  </div>
</div>
<script>
function goToPuzzles() {
  window.scrollTo({ top: document.getElementById('pazuru').offsetTop, behavior: 'smooth' });
} 
</script>
@endif