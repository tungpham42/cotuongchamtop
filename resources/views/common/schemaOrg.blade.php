@php
$onlineBoardGameRating = \Spatie\SchemaOrg\Schema::aggregateRating()
    ->ratingValue(4.7)
    ->bestRating(5)
    ->worstRating(1)
    ->ratingCount(500);
$onlineBoardGame = \Spatie\SchemaOrg\Schema::videoGame()
    ->name('Virtual Xiangqi Board Game')
    ->gamePlatform('Online')
    ->aggregateRating($onlineBoardGameRating);
@endphp
{!! $onlineBoardGame->toScript() !!}
@php
$onlineOrganization = \Spatie\SchemaOrg\Schema::Organization()
    ->name('Cờ tướng')
    ->url('https://cotuong.top/')
    ->description('Cùng chơi với nhiều tính năng hấp dẫn như cờ tướng 2 người, cờ tướng online, chơi cờ tướng với máy, cờ thế và Thi đấu xếp hạng!')
    ->address('Ho Chi Minh City, Vietnam, 700000')
    ->email('cotuongdottop@gmail.com')
    ->contactPoint(\Spatie\SchemaOrg\Schema::contactPoint()->areaServed('Worldwide'))
    ->sameAs([
        'https://en.cotuong.top/',
        'https://www.facebook.com/CoTuongPage',
        'https://www.facebook.com/groups/HoiChoiCoTuong',
        'https://twitter.com/cotuongdottop',
        'https://www.linkedin.com/company/cotuong/'
    ]);
@endphp
{!! $onlineOrganization->toScript() !!}