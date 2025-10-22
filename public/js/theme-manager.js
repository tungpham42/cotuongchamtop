// Global function to update board theme (direct config update)
window.updateBoardTheme = function() {
  try {
    // Get current theme values
    const boardTheme = document.getElementById('boardTheme')?.value || 'xiangqi-board';
    const piecesTheme = document.getElementById('piecesTheme')?.value || 'wiki';
    const piecesUrl = document.getElementById('piecesUrl')?.value || '';
    
    console.log('Updating board theme:', boardTheme, piecesTheme);
    
    // Try to get board instance
    let boardInstance = null;
    if (typeof board !== 'undefined') {
      boardInstance = board;
    } else if (typeof window.board !== 'undefined') {
      boardInstance = window.board;
    }
    
    if (boardInstance) {
      try {
        // Get current position to preserve
        const currentPosition = typeof boardInstance.position === 'function' ? 
          boardInstance.position() : null;
        
        // Update board config directly
        if (boardInstance.config) {
          const newBoardTheme = piecesUrl + '/img/xiangqiboards/' + boardTheme + '.svg';
          const newPieceTheme = piecesUrl + '/img/xiangqipieces/' + piecesTheme + '/{piece}.svg';
          
          console.log('New themes:', newBoardTheme, newPieceTheme);
          
          boardInstance.config.boardTheme = newBoardTheme;
          boardInstance.config.pieceTheme = newPieceTheme;
          
          // Force redraw board with new theme
          if (typeof boardInstance.resize === 'function') {
            boardInstance.resize();
          }
          
          // Restore position if we had one
          if (currentPosition && typeof boardInstance.position === 'function') {
            setTimeout(() => {
              boardInstance.position(currentPosition, false);
            }, 100);
          }
          
          console.log('Board theme updated successfully!');
        }
        
      } catch (error) {
        console.log('Board theme update error:', error);
        
        // Fallback: try position trick to force redraw
        if (boardInstance && typeof boardInstance.position === 'function') {
          try {
            const pos = boardInstance.position();
            setTimeout(() => {
              boardInstance.position(pos, false);
            }, 200);
            console.log('Used position trick to refresh board');
          } catch (e) {
            console.log('Position trick failed:', e);
          }
        }
      }
    } else {
      console.log('Board instance not found, theme will apply on next page load');
    }
    
  } catch (error) {
    console.log('Theme update error:', error);
  }
};

// Simple DOMContentLoaded listener - no observers to avoid conflicts
document.addEventListener('DOMContentLoaded', function() {
  console.log('Theme manager loaded - themes will apply on next board recreation');
});