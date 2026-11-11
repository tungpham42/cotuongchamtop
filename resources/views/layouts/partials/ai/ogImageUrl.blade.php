{{-- Open Graph image URL for the AI pages, chosen by difficulty level. --}}
@php
    $ogImagesByLevel = [
        '1' => 'img/og/co-tuong-cap-do-moi-choi.jpg',
        '2' => 'img/og/co-tuong-cap-do-de.jpg',
        '3' => 'img/og/co-tuong-cap-do-binh-thuong.jpg',
        '4' => 'img/og/co-tuong-cap-do-kho.jpg',
        '5' => 'img/og/co-tuong-cap-do-kho-nhat.jpg',
        '8' => 'img/og/co-tuong-dai-kien-tuong.jpg',
    ];
    $ogImageKey = (string) ($level ?? '3');
    $ogImagePath = $ogImagesByLevel[$ogImageKey] ?? $ogImagesByLevel['3'];
@endphp
{{ asset($ogImagePath) }}
