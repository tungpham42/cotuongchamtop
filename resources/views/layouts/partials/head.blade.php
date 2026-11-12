<meta charset="utf-8" >
<title>{{ url()->current() == localized_url('ai.home') ? __('Cờ Tướng Online Miễn Phí – Chơi Cờ Tướng 2 Người, Với Máy & Cờ Thế') : __(':title - Cờ Tướng Online Miễn Phí – Chơi Cờ Tướng 2 Người, Với Máy & Cờ Thế', ['title' => $headTitle]) }}</title>
<meta name="keywords" content="{{ __('meta_keywords') }}" >
<meta property="article:tag" content="{{ __('cờ tướng') }}">
<meta name="description" content="{{ __('meta_desc') }}" >
<meta property="og:title" content="{{ url()->current() == localized_url('ai.home') ? __('Cờ Tướng Online Miễn Phí – Chơi Cờ Tướng 2 Người, Với Máy & Cờ Thế') : __(':title - Cờ Tướng Online Miễn Phí – Chơi Cờ Tướng 2 Người, Với Máy & Cờ Thế', ['title' => $headTitle]) }}" >
<meta property="og:description" content="{{ __('meta_desc') }}" >
@include('common.head')

<style>
    /* Keep the board plain while pointing, selecting and moving pieces. */
    #ban-co, #ban-co:hover, #ban-co:active, #ban-co:focus-within {
        transform: none !important;
        scale: none !important;
        transition: none !important;
        box-shadow: none !important;
    }
    #ban-co .square-2b8ce:hover img { filter: none !important; transform: none !important; }
    #ban-co .highlight1-e13fc,
    #ban-co .highlight2-e0a03 { box-shadow: none !important; background: transparent !important; }
</style>

@include('layouts.partials.boardClickInteraction')
