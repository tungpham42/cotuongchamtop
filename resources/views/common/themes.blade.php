@if (auth()->check())
<input type="hidden" name="boardTheme" id="boardTheme" value="{{ auth()->user()->board_theme ?? 'xiangqi-board' }}" >
<input type="hidden" name="piecesTheme" id="piecesTheme" value="{{ auth()->user()->pieces_theme ?: 'tung' }}" >
@else
<input type="hidden" name="boardTheme" id="boardTheme" value="xiangqi-board" >
<input type="hidden" name="piecesTheme" id="piecesTheme" value="tung" >
@endif
