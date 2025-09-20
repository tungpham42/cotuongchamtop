@php
echo '<?xml version="1.0" encoding="UTF-8"?>'; 
echo '<?xml-stylesheet type="text/xsl" href="/sitemap.xsl"?>';
@endphp
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @php
        $puzzles = App\Http\Controllers\PuzzleController::getSitemapPuzzles();
    @endphp
    
    @foreach($puzzles as $puzzle)
    <url>
        <loc>{{ URL::to('/') }}/the-co/{{ $puzzle->slug }}</loc>
        <lastmod>{{ date('Y-m-d\TH:i:sP', strtotime($puzzle->updated_at)) }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach
</urlset>