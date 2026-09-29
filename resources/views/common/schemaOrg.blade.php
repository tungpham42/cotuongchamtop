@php
$onlineBoardGameRating = \Spatie\SchemaOrg\Schema::aggregateRating()
    ->ratingValue(4.7)
    ->bestRating(5)
    ->worstRating(1)
    ->ratingCount(500);
$onlineBoardGame = \Spatie\SchemaOrg\Schema::videoGame()
    ->name(__('Virtual Xiangqi Board Game'))
    ->gamePlatform('Online')
    ->aggregateRating($onlineBoardGameRating);
@endphp
{!! $onlineBoardGame->toScript() !!}
@php
$onlineOrganization = \Spatie\SchemaOrg\Schema::Organization()
    ->name(__('Cờ tướng'))
    ->url('https://cotuong.top/')
    ->description(__('meta_desc'))
    ->address('Ho Chi Minh City, Vietnam, 700000')
    ->email('cotuongdottop@gmail.com')
    ->contactPoint(\Spatie\SchemaOrg\Schema::contactPoint()->areaServed('Worldwide'))
    ->sameAs([
        'https://cotuong.top/',
        'https://cotuong.top/en',
        'https://cotuong.top/ja',
        'https://cotuong.top/ko',
        'https://cotuong.top/zh',
        'https://www.facebook.com/CoTuongPage',
        'https://www.facebook.com/groups/HoiChoiCoTuong',
        'https://www.youtube.com/@CoTuongVlog/shorts',
        'https://www.tiktok.com/@cotuongchamtop',
        'https://www.linkedin.com/company/cotuong/',
    ]);
@endphp
{!! $onlineOrganization->toScript() !!}
