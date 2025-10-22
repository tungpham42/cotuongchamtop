// Global function to update board theme (direct config update)
window.updateBoardTheme = function() {
  try {
    // Get current theme values (with localStorage fallback for guests)
    let boardTheme = document.getElementById('boardTheme')?.value || 'xiangqi-board';
    let piecesTheme = document.getElementById('piecesTheme')?.value || 'wiki';
    const piecesUrl = document.getElementById('piecesUrl')?.value || '';
    
    // For guest users, check localStorage if inputs are still default
    if (boardTheme === 'xiangqi-board' && localStorage.getItem('guest_board_theme')) {
      boardTheme = localStorage.getItem('guest_board_theme');
    }
    if (piecesTheme === 'wiki' && localStorage.getItem('guest_pieces_theme')) {
      piecesTheme = localStorage.getItem('guest_pieces_theme');
    }
    
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
  console.log('Theme manager loaded');
  
  // For guest users, automatically apply localStorage themes
  const hasGuestBoardTheme = localStorage.getItem('guest_board_theme');
  const hasGuestPiecesTheme = localStorage.getItem('guest_pieces_theme');
  
  if (hasGuestBoardTheme || hasGuestPiecesTheme) {
    console.log('Found guest themes in localStorage, applying...');
    
    // Update hidden inputs to reflect localStorage values
    if (hasGuestBoardTheme && document.getElementById('boardTheme')) {
      document.getElementById('boardTheme').value = hasGuestBoardTheme;
    }
    if (hasGuestPiecesTheme && document.getElementById('piecesTheme')) {
      document.getElementById('piecesTheme').value = hasGuestPiecesTheme;
    }
    
    // Wait for board to be initialized then apply theme
    setTimeout(() => {
      if (typeof window.updateBoardTheme === 'function') {
        window.updateBoardTheme();
      }
    }, 1000);
  }
});