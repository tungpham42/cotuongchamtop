<meta charset="utf-8" >
<title>{{ url()->current() == localized_url('ai.home') ? __('Cờ Tướng Online Miễn Phí – Chơi Cờ Tướng 2 Người, Với Máy & Cờ Thế') : __(':title - Cờ Tướng Online Miễn Phí – Chơi Cờ Tướng 2 Người, Với Máy & Cờ Thế', ['title' => $headTitle]) }}</title>
<meta name="keywords" content="{{ __('meta_keywords') }}" >
<meta property="article:tag" content="{{ __('cờ tướng') }}">
<meta name="description" content="{{ __('Cùng chơi với nhiều tính năng hấp dẫn như cờ tướng 2 người, cờ tướng online, chơi cờ tướng với máy, cờ thế và Thi đấu xếp hạng!') }}" >
<meta property="og:title" content="{{ url()->current() == localized_url('ai.home') ? __('Cờ Tướng Online Miễn Phí – Chơi Cờ Tướng 2 Người, Với Máy & Cờ Thế') : __(':title - Cờ Tướng Online Miễn Phí – Chơi Cờ Tướng 2 Người, Với Máy & Cờ Thế', ['title' => $headTitle]) }}" >
<meta property="og:description" content="{{ __('Cùng chơi với nhiều tính năng hấp dẫn như cờ tướng 2 người, cờ tướng online, chơi cờ tướng với máy, cờ thế và Thi đấu xếp hạng!') }}" >
@include('common.head')
