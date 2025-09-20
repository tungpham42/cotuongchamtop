<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Canvas</title>

    <link rel="stylesheet" type="text/css" href="{{ mix('css/canvas-ui.css') }}">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="stylesheet" href="//fonts.googleapis.com/css2?family=Karla&family=Merriweather:wght@400;700&display=swap">

    <link rel="stylesheet" href="//cdn.jsdelivr.net/gh/highlightjs/cdn-release@10.1.2/build/styles/github.min.css">
</head>
<body class="mb-5">

<div id="ui">
    <router-view></router-view>
</div>

<script>
    window.CanvasUI = @json($config);
</script>

<script type="text/javascript" src="{{ mix('js/canvas-ui.js') }}"></script>
</body>
</html>

