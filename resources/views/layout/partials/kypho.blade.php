<div id="kypho-panel" class="kypho-panel">
  <div class="kypho-header">
    <h5 class="text-center my-2"><i class="fad fa-list"></i> {{ __("Kỳ phổ") }}</h5>
  </div>
  <div class="kypho-controls text-center mb-2">
    <button id="kypho-prev" type="button" class="btn btn-dark btn-sm mx-1" title="Nước trước">
      <i class="fad fa-step-backward"></i> {{ __("Trước") }}
    </button>
    <button id="kypho-play" type="button" class="btn btn-danger btn-sm mx-1" title="{{ __("Phát kỳ phổ") }}">
      <i class="fad fa-play"></i> {{ __("Phát") }}
    </button>
    <button id="kypho-pause" type="button" class="btn btn-dark btn-sm mx-1" title="{{ __("Tạm dừng") }}">
      <i class="fad fa-pause"></i> {{ __("Tạm dừng") }}
    </button>
    <button id="kypho-next" type="button" class="btn btn-dark btn-sm mx-1" title="Nước sau">
      <i class="fad fa-step-forward"></i> {{ __("Sau") }}
    </button>
    <button id="kypho-copy" type="button" class="btn btn-danger btn-sm mx-1" title="{{ __("Sao chép kỳ phổ") }}">
      <i class="fad fa-copy"></i> {{ __("Sao chép") }}
    </button>
  </div>
  <div class="kypho-body">
    <div id="kypho-list" class="kypho-moves"></div>
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
  }
  .kypho-move {
    display: inline-flex;
    align-items: center;
    padding: 2px 6px;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.06);
    font-size: 0.8rem;
    line-height: 1.1;
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
