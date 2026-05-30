<meta charset="utf-8">
<meta property="article:tag" content="{{ __('cờ tướng') }}">
<meta property="og:image" content="@yield('og_image', url('/') . '/img/1200x630.jpg')">
<meta property="og:image:width" content="@yield('og_image_width', '1200')" >
<meta property="og:image:height" content="@yield('og_image_height', '630')" >
<meta property="og:image:alt" content="@yield('og_image_alt', __('Cờ tướng 2 người'))" >
<meta property="og:image:type" content="@yield('og_image_type', 'image/jpeg')" />
@hasSection('meta_description')
<meta name="description" content="@yield('meta_description')" >
<meta property="og:description" content="@yield('meta_description')" >
@else
<meta name="description" content="{{ __('Cùng chơi với nhiều tính năng hấp dẫn như cờ tướng 2 người, cờ tướng online, chơi cờ tướng với máy, cờ thế và Thi đấu xếp hạng!') }}" >
<meta property="og:description" content="{{ __('Cùng chơi với nhiều tính năng hấp dẫn như cờ tướng 2 người, cờ tướng online, chơi cờ tướng với máy, cờ thế và Thi đấu xếp hạng!') }}" >
@endif

@php
    // Fetch title from passed variable or route defaults
    $siteTitle = $headTitle ?? request()->route('headTitle') ?? __('Thi đấu xếp hạng');

    // Append the dynamic query if on the search route
    if (request()->routeIs('search', '*.search') && request()->filled('query')) {
        $siteTitle = __('Kết quả tìm kiếm cho từ khóa ":query"', ['query' => request()->query('query')]);
    }
@endphp

<meta property="og:title" content="{{ __(':title - Cờ tướng 2 người, đánh cờ tướng online, chơi cờ tướng với máy miễn phí', ['title' => $siteTitle]) }}" >
<title>{{ __(':title - Cờ tướng 2 người, đánh cờ tướng online, chơi cờ tướng với máy miễn phí', ['title' => $siteTitle]) }}</title>
@include('common.head')
@include('common.scripts')
<script>
var locale = {
    OK: '<i class="fas fa-check"></i> {{ __('Đồng ý') }}',
    CONFIRM: '<i class="fas fa-check"></i> {{ __('Chấp nhận') }}',
    CANCEL: '<i class="fas fa-times"></i> {{ __('Hủy') }}'
};
bootbox.addLocale('{{ app()->getLocale() }}', locale);
function time()
{
    var timestamp = Math.floor(new Date().getTime() / 1000);
    return timestamp;
}
function get_gravatar_image_url(email, size, default_image, allowed_rating, force_default)
{
    email = typeof email !== 'undefined' ? email : 'john.doe@example.com';
    size = (size >= 1 && size <= 2048) ? size : 80;
    default_image = typeof default_image !== 'undefined' ? default_image : 'retro';
    allowed_rating = typeof allowed_rating !== 'undefined' ? allowed_rating : 'g';
    force_default = force_default === true ? 'y' : 'n';

    return ("https://secure.gravatar.com/avatar/" + CryptoJS.MD5(email.toLowerCase().trim()) + "?size=" + size + "&default=" + encodeURIComponent(default_image) + "&rating=" + allowed_rating + (force_default === 'y' ? "&forcedefault=" + force_default : ''));
}
</script>
<script src="{{ url('/') }}/js/xiangqiboard.js?v=31"></script>
