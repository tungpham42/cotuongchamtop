<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<div id="kypho-panel" class="kypho-panel">
  <div class="kypho-header">
    <h5 class="text-center my-2"><i class="fad fa-list"></i> {{ __("Kỳ phổ") }}</h5>
  </div>
  <div class="kypho-controls text-center mb-2">
    <button id="kypho-prev" type="button" class="btn btn-dark btn-sm mx-1" title="Nước trước">
      <i class="fad fa-step-backward"></i> {{ __("Trước") }}
    </button>
    <button id="kypho-next" type="button" class="btn btn-dark btn-sm mx-1" title="Nước sau">
      <i class="fad fa-step-forward"></i> {{ __("Sau") }}
    </button>
  </div>
  <div class="kypho-body">
    <div id="kypho-list" class="kypho-moves" tabindex="0" role="region" aria-label="{{ __('Danh sách nước đi') }}"></div>
  </div>
</div>
<style>
  .kypho-panel {
    margin: 4px auto 0;
    max-width: 520px;
  }
  .kypho-header h5 {
    font-size: 0.95rem;
    margin: 0 0 4px;
  }
  .kypho-controls {
    margin-bottom: 4px !important;
  }
  .kypho-controls .btn {
    padding: 0.1rem 0.45rem;
    font-size: 0.75rem;
  }
  .kypho-moves {
    display: flex;
    flex-wrap: wrap;
    gap: 4px 6px;
    align-items: center;
    align-content: flex-start;
    box-sizing: border-box;
    /* Three 24px rows, two 4px gaps, padding and border. */
    max-height: 98px;
    overflow-y: auto;
    overscroll-behavior-y: contain;
    scrollbar-width: thin;
    padding: 8px;
    border: 1px solid rgba(255, 255, 255, 0.16);
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.18);
  }
  .kypho-move {
    display: inline-flex;
    align-items: center;
    padding: 2px 6px;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.06);
    font-size: 0.8rem;
    line-height: 20px;
    height: 24px;
    box-sizing: border-box;
    flex-shrink: 0;
    white-space: nowrap;
  }
  .kypho-move-number {
    font-size: 0.7rem;
    color: #a9b1bb;
    margin-right: 4px;
  }
  .kypho-move.red {
    color: #ff6b6b;
  }
  .kypho-move.black {
    color: #cbd3da;
  }
  .kypho-move.kypho-current {
    background-color: rgba(255, 255, 255, 0.85);
    color: #111;
    font-weight: 700;
  }
  .kypho-controls .btn[disabled],
  .kypho-controls .btn.disabled {
    opacity: 0.5;
    pointer-events: none;
  }
</style>
