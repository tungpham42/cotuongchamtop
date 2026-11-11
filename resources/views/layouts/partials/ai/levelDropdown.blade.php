{{--
    Difficulty heading + level dropdown.

    Params:
      routePrefix      'ai' | 'board.ai'   route-name prefix; routes are "<prefix>.newbie" ... "<prefix>.hardest" (+ ".master")
      buttonLabel      translated text of the dropdown button
      withMaster       (bool, default false) append the "Kiện tướng" item
      matchByLevelTxt  (bool, default false) mark the active item by $levelTxt instead of the current URL
                       (needed when the URL carries a FEN and therefore never matches request()->is())
      carriesFen       (bool, default false) give items the "add-fen" class so the script appends the current FEN to their href
--}}
@php
    $withMaster      = $withMaster ?? false;
    $matchByLevelTxt = $matchByLevelTxt ?? false;
    $carriesFen      = $carriesFen ?? false;

    $levelItems = [
        ['key' => 'newbie',  'label' => 'Mới chơi'],
        ['key' => 'easy',    'label' => 'Dễ'],
        ['key' => 'normal',  'label' => 'Bình thường'],
        ['key' => 'hard',    'label' => 'Khó'],
        ['key' => 'hardest', 'label' => 'Khó nhất'],
    ];
    if ($withMaster) {
        $levelItems[] = ['key' => 'master', 'label' => 'Kiện tướng', 'master' => true];
    }

    // localized_url() already returns the URL for the current locale. Reduce it to a path without a
    // leading slash so request()->is() and url('/' . $path) work for either a full URL or a path.
    $levelItems = array_map(function ($item) use ($routePrefix, $matchByLevelTxt, $levelTxt) {
        $item['path'] = ltrim((string) parse_url(localized_url($routePrefix . '.' . $item['key']), PHP_URL_PATH), '/');
        $item['active'] = $matchByLevelTxt ? $levelTxt === $item['label'] : request()->is($item['path']);
        return $item;
    }, $levelItems);
@endphp
<h5 class="text-center my-1">{{ __("Cấp độ") }}: {{ __($levelTxt) }}</h5>
<div class="level dropup mx-auto text-center my-1">
    <button class="btn btn-lg btn-dark dropdown-toggle" type="button" id="levelDropdown" data-toggle="dropdown" aria-haspopup="true" data-step="1" data-intro="Hãy chọn cấp độ phù hợp với bạn nhé" aria-expanded="false">
        <i class="fad fa-robot"></i> {{ $buttonLabel }}
    </button>
    <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="levelDropdown">
        @foreach ($levelItems as $item)
            @if (!empty($item['master']))
                <div class="dropdown-divider"></div>
            @endif
            <a class="{{ $carriesFen ? 'add-fen ' : '' }}dropdown-item{{ !empty($item['master']) ? ' font-weight-bold' : '' }}{{ $item['active'] ? ' active disabled' : '' }}" href="{{ url('/' . $item['path']) }}" style="cursor: pointer !important;">{{ __($item['label']) }}</a>
        @endforeach
    </div>
</div>
