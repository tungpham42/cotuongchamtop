{{-- @php
if ( Request::get('page') <= ceil($puzzles->total() / $puzzles->perPage()) ):
@endphp
<span style="background-color: #312e2b; margin-top: -15px;" class="d-block w-100 pb-5 mb-5" id="mi"></span>
<div style="background-color: #312e2b" class="container-fluid puzzles px-0">
    <div class="container mx-auto px-3 pt-0">
        <div class="row my-0">
            <h2 class="d-block w-100 text-light ml-3 mb-4"><i class="fas fa-puzzle-piece"></i> {{ numberToWordsZh($puzzles->total()) }}个令人惊叹的谜题</h2>
            {{ $puzzles->links('vendor.pagination.zh') }}
            @foreach($puzzles as $puzzle)
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                <div class="card shadow-lg rounded border-dark">
                    @if ($puzzle->post_excerpt)
                        <img onclick="setupPuzzle('{{ $puzzle->post_excerpt }}')" class="card-img-top" style="cursor: pointer;" src="{{ $puzzle->getImageAttribute() }}" alt="{{ $puzzle->post_title }}" data-toggle="tooltip" data-placement="top" title='玩这个谜题'>
                    @else
                        <a class="stopPromotion" target="_blank" href="https://blog.cotuong.top/the-co-dac-sac/{{ $puzzle->post_name }}"><img class="card-img-top" src="{{ $puzzle->getImageAttribute() }}" alt="{{ $puzzle->post_title }}"></a>
                    @endif
                </div>
            </div>
            @endforeach
            {{ $puzzles->links('vendor.pagination.zh') }}
        </div>
    </div>
    <script>
    function setupPuzzle(fenCode) {
        savedFenCode = fenCode;
        window.scrollTo({ top: 0 });
        board.position(fenCode);
        game.load(fenCode + ' r - - 0 1');
        $('#game-status').removeClass('black').addClass('red');
        updateStatus();
        $('#game-over').removeClass('d-inline-block').addClass('d-none');
        $('#resign').removeClass('disabled').attr('aria-disabled', false);
        config.draggable = true;
    }
    </script>
</div>
@php
endif;
@endphp --}}