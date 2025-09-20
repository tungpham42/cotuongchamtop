<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TitleController extends Controller
{
    public function fetchTitle(Request $request)
    {
        $url = $request->input('url');

        // Fetch the HTML content of the URL
        $html = file_get_contents($url);

        // Extract the title tag using DOMDocument
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $titleNodes = $dom->getElementsByTagName('title');
        $title = $titleNodes->length > 0 ? $titleNodes->item(0)->nodeValue : '';

        return response()->json(['title' => $title]);
    }
}
