@if(isset($_GET['loai']) && ($_GET['loai'] == 'van-da-dau' || $_GET['loai'] == 'van-dau' || $_GET['loai'] == 'the-co'))
<span style="background-color: transparent; margin-top: -70px;" class="d-block w-100 pb-5 mb-5" id="co-the"></span>
<div style="background-color: transparent" class="container-fluid puzzles px-0">
    <div class="container mx-auto px-3 pt-0">
        <div class="row my-0">
            <h2 class="d-block w-100 text-light ml-3 mb-4"><i class="fas fa-puzzle-piece"></i> {{ numberToWordsVi($puzzles->total()) }} thế cờ đặc sắc</h2>
            {{ $firstPagePuzzles->links('vendor.pagination.vi') }}
            @foreach($firstPagePuzzles as $puzzle)
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                <div class="card shadow-lg rounded border-dark">
                    @if ($puzzle->post_excerpt)
                        <img onclick="setupPuzzle('{{ $puzzle->post_excerpt }}', '{{ $puzzle->post_title }}')" class="card-img-top" style="cursor: pointer;" src="{{ $puzzle->getImageAttribute() }}" alt="{{ $puzzle->post_title }}" data-toggle="tooltip" data-placement="top" title='Chơi thế cờ "{{ $puzzle->post_title }}"'>
                        <div class="card-body bg-dark p-2">
                            <h5 onclick="setupPuzzle('{{ $puzzle->post_excerpt }}', '{{ $puzzle->post_title }}')" class="mx-auto card-title text-light m-0 font-weight-light text-center" style="cursor: pointer;" data-toggle="tooltip" data-placement="top" title='Chơi thế cờ "{{ $puzzle->post_title }}"'>{{ $puzzle->post_title }}</h5>
                        </div>
                        <div class="row mx-0">
                            <a class="col-4 btn btn-dark btn-sm text-light setup-puzzle-btn" href="javascript:backToPuzzle('{{ $puzzle->post_excerpt }}')" data-toggle="tooltip" data-placement="top" title='Xếp thế cờ "{{ $puzzle->post_title }}"'><i class="fad fa-puzzle-piece"></i></a>
                            <a class="col-4 btn btn-dark btn-sm text-light solve-puzzle-btn" href="javascript:solvePuzzle('{{ $puzzle->post_excerpt }}')" data-toggle="tooltip" data-placement="top" title='Giải thế cờ "{{ $puzzle->post_title }}"'><i class="fad fa-abacus"></i></a>
                            <a class="col-4 btn btn-dark btn-sm text-light stopPromotion puzzle-hint-btn" target="_blank" href="https://blog.cotuong.top/the-co-dac-sac/{{ $puzzle->post_name }}" data-toggle="tooltip" data-placement="top" title='Lời giải của thế cờ "{{ $puzzle->post_title }}"'><i class="fad fa-info-square"></i></a>
                        </div>
                    @else
                        <a class="stopPromotion" target="_blank" href="https://blog.cotuong.top/the-co-dac-sac/{{ $puzzle->post_name }}"><img class="card-img-top" src="{{ $puzzle->getImageAttribute() }}" alt="{{ $puzzle->post_title }}"></a>
                        <div class="card-body bg-dark p-2">
                            <a class="text-light stopPromotion" target="_blank" href="https://blog.cotuong.top/the-co-dac-sac/{{ $puzzle->post_name }}"><h5 class="card-title text-light m-0 font-weight-light text-center">{{ $puzzle->post_title }}</h5></a>
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
            {{ $firstPagePuzzles->links('vendor.pagination.vi') }}
        </div>
    </div>
    <script>
    window.onload = function() {
        var puzzlesJSONstring = '{{ $fenCodes }}';
        puzzlesJSONstring = puzzlesJSONstring.replace(/&quot;/ig,'"');
        var puzzlesObject = JSON.parse(puzzlesJSONstring);
        var puzzlesCount = puzzlesObject.length;
        for (var i = 0; i < puzzlesCount; i++) {
            if (puzzlesObject[i].fenCode == board.fen()) {
                $('#puzzle-title').html("&nbsp;thế&nbsp;cờ&nbsp;" + '"' + puzzlesObject[i].puzzleTitle + '"').removeClass('d-none').addClass('d-inline-block');
            }
        }
    }
    var savedFenCode = '';
    function setupPuzzle(fenCode, puzzleTitle) {
        savedFenCode = fenCode;
        // window.scrollTo({ top: 0, behavior: 'smooth' });
        window.scrollTo({ top: 0 });
        $('#puzzle-title').html("&nbsp;thế&nbsp;cờ&nbsp;" + '"' + puzzleTitle + '"').removeClass('d-none').addClass('d-inline-block');
        board.position(fenCode);
        game.load(fenCode + ' r - - 0 1');
        $('#game-status').removeClass('black').addClass('red');
        updateStatus();
        $('#game-over').removeClass('d-inline-block').addClass('d-none');
        $('#resign').removeClass('disabled').attr('aria-disabled', false);
        config.draggable = true;
    }
    $('#reset').on('click', function(){
        if ($('#puzzle-title').html() != '') {
            window.scrollTo({ top: 0 });
            board.position(savedFenCode, false);
            game.load(savedFenCode + ' r - - 0 1');
            $('#game-status').removeClass('black').addClass('red');
            updateStatus();
            $('#game-over').removeClass('d-inline-block').addClass('d-none');
            $('#resign').removeClass('disabled').attr('aria-disabled', false);
            config.draggable = true;
        }
    });
    function backToPuzzle(fenCode) {
        if (!game.validate_fen(fenCode + ' r - - 0 1').valid) {
            bootbox.alert({
            message: "Bàn cờ thế không hợp lệ",
            locale: 'vi',
            centerVertical: true,
            closeButton: false,
            buttons: {
                ok: {
                className: 'btn-danger pulse-red'
                }
            }
            });
        } else {
            // $('#AdSenseModal').attr('data-url', '{{ url('/co-the') }}/' + fenCode).modal('show');
            window.location.href = '{{ url('/co-the') }}/' + fenCode;
        }
    }
    function solvePuzzle(fenCode) {
        if (!game.validate_fen(fenCode + ' r - - 0 1').valid) {
            bootbox.alert({
            message: "Bàn cờ thế không hợp lệ",
            locale: 'vi',
            centerVertical: true,
            closeButton: false,
            buttons: {
                ok: {
                className: 'btn-danger pulse-red'
                }
            }
            });
        } else {
            // $('#AdSenseModal').attr('data-url', '{{ url('/giai-co-the') }}/' + fenCode + ' r - - 0 1').modal('show');
            window.location.href = '{{ url('/giai-co-the') }}/' + fenCode + ' r - - 0 1';
        }
    }
    </script>
</div>
@else
    @if ( Request::get('page') <= ceil($puzzles->total() / $puzzles->perPage()) )
    <span style="background-color: transparent; margin-top: -70px;" class="d-block w-100 pb-5 mb-5" id="co-the"></span>
    <div style="background-color: transparent" class="container-fluid puzzles px-0">
        <div class="container mx-auto px-3 pt-0">
            <div class="row my-0">
                <h2 class="d-block w-100 text-light ml-3 mb-4"><i class="fas fa-puzzle-piece"></i> {{ numberToWordsVi($puzzles->total()) }} thế cờ đặc sắc</h2>
                {{ $puzzles->links('vendor.pagination.vi') }}
                @foreach($puzzles as $puzzle)
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                    <div class="card shadow-lg rounded border-dark">
                        @if ($puzzle->post_excerpt)
                            <img onclick="setupPuzzle('{{ $puzzle->post_excerpt }}', '{{ $puzzle->post_title }}')" class="card-img-top" style="cursor: pointer;" src="{{ $puzzle->getImageAttribute() }}" alt="{{ $puzzle->post_title }}" data-toggle="tooltip" data-placement="top" title='Chơi thế cờ "{{ $puzzle->post_title }}"'>
                            <div class="card-body bg-dark p-2">
                                <h5 onclick="setupPuzzle('{{ $puzzle->post_excerpt }}', '{{ $puzzle->post_title }}')" class="mx-auto card-title text-light m-0 font-weight-light text-center" style="cursor: pointer;" data-toggle="tooltip" data-placement="top" title='Chơi thế cờ "{{ $puzzle->post_title }}"'>{{ $puzzle->post_title }}</h5>
                            </div>
                            <div class="row mx-0">
                                <a class="col-4 btn btn-dark btn-sm text-light setup-puzzle-btn" href="javascript:backToPuzzle('{{ $puzzle->post_excerpt }}')" data-toggle="tooltip" data-placement="top" title='Xếp thế cờ "{{ $puzzle->post_title }}"'><i class="fad fa-puzzle-piece"></i></a>
                                <a class="col-4 btn btn-dark btn-sm text-light solve-puzzle-btn" href="javascript:solvePuzzle('{{ $puzzle->post_excerpt }}')" data-toggle="tooltip" data-placement="top" title='Giải thế cờ "{{ $puzzle->post_title }}"'><i class="fad fa-abacus"></i></a>
                                <a class="col-4 btn btn-dark btn-sm text-light stopPromotion puzzle-hint-btn" target="_blank" href="https://blog.cotuong.top/the-co-dac-sac/{{ $puzzle->post_name }}" data-toggle="tooltip" data-placement="top" title='Lời giải của thế cờ "{{ $puzzle->post_title }}"'><i class="fad fa-info-square"></i></a>
                            </div>
                        @else
                            <a class="stopPromotion" target="_blank" href="https://blog.cotuong.top/the-co-dac-sac/{{ $puzzle->post_name }}"><img class="card-img-top" src="{{ $puzzle->getImageAttribute() }}" alt="{{ $puzzle->post_title }}"></a>
                            <div class="card-body bg-dark p-2">
                                <a class="text-light stopPromotion" target="_blank" href="https://blog.cotuong.top/the-co-dac-sac/{{ $puzzle->post_name }}"><h5 class="card-title text-light m-0 font-weight-light text-center">{{ $puzzle->post_title }}</h5></a>
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
                {{ $puzzles->links('vendor.pagination.vi') }}
            </div>
        </div>
        <script>
        window.onload = function() {
            var puzzlesJSONstring = '{{ $fenCodes }}';
            puzzlesJSONstring = puzzlesJSONstring.replace(/&quot;/ig,'"');
            var puzzlesObject = JSON.parse(puzzlesJSONstring);
            var puzzlesCount = puzzlesObject.length;
            for (var i = 0; i < puzzlesCount; i++) {
                if (puzzlesObject[i].fenCode == board.fen()) {
                    $('#puzzle-title').html("&nbsp;thế&nbsp;cờ&nbsp;" + '"' + puzzlesObject[i].puzzleTitle + '"').removeClass('d-none').addClass('d-inline-block');
                }
            }
        }
        var savedFenCode = '';
        function setupPuzzle(fenCode, puzzleTitle) {
            savedFenCode = fenCode;
            // window.scrollTo({ top: 0, behavior: 'smooth' });
            window.scrollTo({ top: 0 });
            $('#puzzle-title').html("&nbsp;thế&nbsp;cờ&nbsp;" + '"' + puzzleTitle + '"').removeClass('d-none').addClass('d-inline-block');
            board.position(fenCode);
            game.load(fenCode + ' r - - 0 1');
            $('#game-status').removeClass('black').addClass('red');
            updateStatus();
            $('#game-over').removeClass('d-inline-block').addClass('d-none');
            $('#resign').removeClass('disabled').attr('aria-disabled', false);
            config.draggable = true;
        }
        $('#reset').on('click', function(){
            if ($('#puzzle-title').html() != '') {
                window.scrollTo({ top: 0 });
                board.position(savedFenCode, false);
                game.load(savedFenCode + ' r - - 0 1');
                $('#game-status').removeClass('black').addClass('red');
                updateStatus();
                $('#game-over').removeClass('d-inline-block').addClass('d-none');
                $('#resign').removeClass('disabled').attr('aria-disabled', false);
                config.draggable = true;
            }
        });
        function backToPuzzle(fenCode) {
            if (!game.validate_fen(fenCode + ' r - - 0 1').valid) {
                bootbox.alert({
                message: "Bàn cờ thế không hợp lệ",
                locale: 'vi',
                centerVertical: true,
                closeButton: false,
                buttons: {
                    ok: {
                    className: 'btn-danger pulse-red'
                    }
                }
                });
            } else {
                // $('#AdSenseModal').attr('data-url', '{{ url('/co-the') }}/' + fenCode).modal('show');
                window.location.href = '{{ url('/co-the') }}/' + fenCode;
            }
        }
        function solvePuzzle(fenCode) {
            if (!game.validate_fen(fenCode + ' r - - 0 1').valid) {
                bootbox.alert({
                message: "Bàn cờ thế không hợp lệ",
                locale: 'vi',
                centerVertical: true,
                closeButton: false,
                buttons: {
                    ok: {
                    className: 'btn-danger pulse-red'
                    }
                }
                });
            } else {
                // $('#AdSenseModal').attr('data-url', '{{ url('/giai-co-the') }}/' + fenCode + ' r - - 0 1').modal('show');
                window.location.href = '{{ url('/giai-co-the') }}/' + fenCode + ' r - - 0 1';
            }
        }
        </script>
    </div>
    @endif
@endif