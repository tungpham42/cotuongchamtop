<meta charset="utf-8">
<meta property="article:tag" content="cờ tướng">
@php
if (str_contains(url()->current(), '/ky-thu/') && isset($player)):
    Avatar::create($player->name)->setDimension(200)->setFontSize(100)->save(public_path().'/players/'.md5($player->email).'.jpg', 100);
    $avatar_image_url = url('/').'/players/'.md5($player->email).'.jpg';
@endphp
    <meta property="og:image" content="{{ $avatar_image_url }}">
@php
else:
@endphp
<meta property="og:image" content="{{ url('/') }}/img/1200x630.jpg">
<meta property="og:image:width" content="1200" >
<meta property="og:image:height" content="630" >
<meta property="og:image:alt" content="Cờ tướng 2 nguòi" >
@php
endif
@endphp
<meta name="description" content="Cùng chơi với nhiều tính năng hấp dẫn như cờ tướng 2 người, cờ tướng online, chơi cờ tướng với máy, cờ thế và Thi đấu xếp hạng!" >
<meta property="og:description" content="Cùng chơi với nhiều tính năng hấp dẫn như cờ tướng 2 người, cờ tướng online, chơi cờ tướng với máy, cờ thế và Thi đấu xếp hạng!" >
@php
    $siteTitle = '';
    if (isset($headTitle)) {
        $siteTitle = $headTitle;
    } elseif (url()->current() == url('/dang-nhap')) {
        $siteTitle = 'Đăng nhập';
    } elseif (url()->current() == url('/dang-ky')) {
        $siteTitle = 'Đăng ký';
    } elseif (url()->current() == url('/quen-mat-khau')) {
        $siteTitle = 'Quên mật khẩu';
    } elseif (url()->current() == url('/tao-mat-khau')) {
        $siteTitle = 'Tạo mật khẩu';
    } elseif (url()->current() == url('/tim-kiem')) {
        if (isset($_GET['query']) && $_GET['query'] != '') {
            $siteTitle = 'Kết quả tìm kiếm cho từ khóa "'.$_GET['query'].'"';
        } else {
            $siteTitle = 'Tìm kiếm kỳ thủ';
        }
    } elseif (str_contains(url()->current(), url('/dat-lai-mat-khau').'/')) {
        $siteTitle = 'Đặt lại mật khẩu';
    } else {
        $siteTitle = 'Thi đấu xếp hạng';
    }
@endphp
<meta property="og:title" content="{{ $siteTitle }} - Cờ tướng 2 người, đánh cờ tướng online, chơi cờ tướng với máy miễn phí" >
<title>{{ $siteTitle }} - Cờ tướng 2 người, đánh cờ tướng online, chơi cờ tướng với máy miễn phí</title>
@include('common.head')
@include('common.scripts')
<script>
var locale = {
    OK: '<i class="fas fa-check"></i> Đồng ý',
    CONFIRM: '<i class="fas fa-check"></i> Chấp nhận',
    CANCEL: '<i class="fas fa-times"></i> Hủy'
};
bootbox.addLocale('vi', locale);
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