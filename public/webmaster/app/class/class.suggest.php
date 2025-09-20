<?php
class Suggest {
    private static $googleSuggest = 'https://suggestqueries.google.com/complete/search?output=firefox&client=firefox&q=%s&hl=%s';

    public static function google($keyword, $lang) {
        $keywords = array();
        $url = sprintf(self::$googleSuggest, urlencode($keyword), $lang);
        if(!$response = HttpCurl::curl($url)) {
            return array();
        }
        if (($data = @json_decode($response, true)) !== null) {
            $keywords = isset($data[1]) ? $data[1] : array();
        }
        return $keywords;
    }
}