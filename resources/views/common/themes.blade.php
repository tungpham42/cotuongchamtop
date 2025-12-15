@if (auth()->check())
<input type="hidden" name="boardTheme" id="boardTheme" value="{{ auth()->user()->board_theme ?? 'xiangqi-board' }}" >
<input type="hidden" name="piecesTheme" id="piecesTheme" value="{{ auth()->user()->pieces_theme ?: 'wiki' }}" >
@else
<input type="hidden" name="boardTheme" id="boardTheme" value="xiangqi-board" >
<input type="hidden" name="piecesTheme" id="piecesTheme" value="wiki" >
@endif

<script>
// Load guest user theme preferences from localStorage
@if (!auth()->check())
// Immediate script - run as soon as possible
(function() {
  const savedBoardTheme = localStorage.getItem('guest_board_theme');
  const savedPiecesTheme = localStorage.getItem('guest_pieces_theme');

  if (savedBoardTheme || savedPiecesTheme) {
    console.log('Guest themes found:', savedBoardTheme, savedPiecesTheme);

    // Set up a watcher for when inputs are created
    const checkAndSetInputs = function() {
      const boardInput = document.getElementById('boardTheme');
      const piecesInput = document.getElementById('piecesTheme');

      if (boardInput && piecesInput) {
        if (savedBoardTheme) boardInput.value = savedBoardTheme;
        if (savedPiecesTheme) piecesInput.value = savedPiecesTheme;
        console.log('Guest theme inputs updated');
        return true;
      }
      return false;
    };

    // Try immediately
    if (!checkAndSetInputs()) {
      // If not ready, wait for DOM
      document.addEventListener('DOMContentLoaded', function() {
        checkAndSetInputs();
      });
    }
  }
})();
@endif
</script>
