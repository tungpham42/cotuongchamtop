@extends('layout.app')

@section('content')
<div class="container">
    <div class="row justify-content-center text-center mb-4">
        <div class="col-12">
            <!-- CO_res -->
            <ins class="adsbygoogle"
            style="display:block"
            data-ad-client="ca-pub-3585118770961536"
            data-ad-slot="7831723879"
            data-ad-format="auto"
            data-full-width-responsive="true"></ins>
            <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-palette"></i> Thay đổi giao diện
                    @include('layout.partials.app.tourBtn')
                </div>
                <div class="card-body">
                    <div class="col-md-12">
                        <form method="POST" action="{{ route('change.ui') }}">
                            @csrf
                            <input type="hidden" value="{{ auth()->user()->id }}" name="current_id">
                            <div class="form-group">
                                <label for="board_theme">Bàn cờ</label>
                                <select data-step="1" data-intro="Chọn giao diện bàn cờ" class="form-control form-control-lg" name="board_theme" id="board_theme">
                                    <option value="xiangqi-board" @if(auth()->user()->board_theme === 'xiangqi-board') selected @endif>Bàn cờ mặc định</option>
                                    <option value="ban-co-go" @if(auth()->user()->board_theme === 'ban-co-go') selected @endif>Gỗ nhạt</option>
                                    <option value="wood-board" @if(auth()->user()->board_theme === 'wood-board') selected @endif>Gỗ đậm</option>
                                    <option value="ban-co" @if(auth()->user()->board_theme === 'ban-co') selected @endif>Vàng chói</option>
                                    <option value="banco" @if(auth()->user()->board_theme === 'banco') selected @endif>Sáng</option>
                                    <option value="chess-board" @if(auth()->user()->board_theme === 'chess-board') selected @endif>Cam nhạt</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="pieces_theme">Quân cờ</label>
                                <select data-step="2" data-intro="Chọn giao diện quân cờ" class="form-control form-control-lg" name="pieces_theme" id="pieces_theme">
                                    <option value="wiki" @if(auth()->user()->pieces_theme === 'wiki') selected @endif>Quân cờ mặc định</option>
                                    <option value="tung" @if(auth()->user()->pieces_theme === 'tung') selected @endif>Đặc biệt</option>
                                    <option value="do-den" @if(auth()->user()->pieces_theme === 'do-den') selected @endif>Đỏ đen</option>
                                    <option value="graphic" @if(auth()->user()->pieces_theme === 'graphic') selected @endif>Phương Tây</option>
                                    <option value="co" @if(auth()->user()->pieces_theme === 'co') selected @endif>Cam</option>
                                    <option value="wikimedia" @if(auth()->user()->pieces_theme === 'wikimedia') selected @endif>Vàng đậm</option>
                                    <option value="quan" @if(auth()->user()->pieces_theme === 'quan') selected @endif>Sáng</option>
                                    <option value="traditional" @if(auth()->user()->pieces_theme === 'traditional') selected @endif>Truyền thống</option>
                                </select>
                            </div>
                            <button data-step="3" data-intro="Ấn vào đây để đổi giao diện" type="submit" class="btn btn-lg btn-danger"><i class="fad fa-palette"></i> Đổi giao diện</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection