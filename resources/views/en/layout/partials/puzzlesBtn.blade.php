@if (!str_contains(url()->current(), '/room/'))
<div class="container">
  <div class="row">
    <a id="puzzlesBtn" href="javascript:goToPuzzles();" class="position-absolute float-left btn btn-danger btn-lg pulse-red" data-toggle="tooltip" data-placement="bottom" title='Go to {{ numberToWordsEn($puzzles->total()) }} amazing puzzles'><i class="fas fa-chevron-double-down"></i></a>
  </div>
</div>
<script>
function goToPuzzles() {
  window.scrollTo({ top: document.getElementById('puzzles').offsetTop, behavior: 'smooth' });
} 
</script>
@endif