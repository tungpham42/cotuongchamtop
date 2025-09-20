<?php
class SearchEngine
{
    private static $glUrl = 'https://www.google.com/search?q=site:%s';
    private static $biUrl = 'https://www.bing.com/search?q=site:%s';
    private static $yaUrl = 'https://search.yahoo.com/bin/search?p=site:%s';
    private static $yanUrl = 'http://webmaster.yandex.ru/check.xml?hostname=%s';
    //private static $googleBacklinkUrl = 'http://www.google.com/search?q=%22http%3a%2f%2f{Domain}%22+-site%3A{Domain}&filter=0';
    // private static $googleBacklinkUrl = 'https://www.google.com/search?q=%22{Domain}%22';
    private static $googleBacklinkUrl = 'https://www.google.com/search?q=%22{Domain}%22+-site%3A{Domain}&filter=0';

    public static function google($domain)
    {
        $url = sprintf(self::$glUrl, $domain);
        if(!$response = HttpCurl::curl($url)) {
            return 0;
        }
        return self::parseResults($response);
    }

    public static function googleBackLinks($domain) {
        $url = strtr(self::$googleBacklinkUrl, array(
            "{Domain}"=>$domain,
        ));
        if(!$response = HttpCurl::curl($url))
            return 0;
        return self::parseResults($response);
    }

    private static function parseResults($html) {
        @preg_match('#<div.*?id="result-stats"[^>]*>(.*?)<\/div[^>]*>#siu', $html, $matches);
        if(isset($matches[1])) {
            $withoutTags = @preg_replace('/<[^>]*>[^<]*<[^>]*>/siu', '', $matches[1]);
            $num = @preg_replace("/\D/u", "", html_entity_decode($withoutTags));
            return (int) $num;
        }
        return 0;
    }

    public static function bing($domain)
    {
        $url = sprintf(self::$biUrl, $domain);
        if(!$response = HttpCurl::curl($url, array(
            'user_agent'=>'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.99 Safari/537.36'
        ), "bing")) {
            return 0;
        }
        //echo $response; die();
        preg_match('#<span.*?class="sb_count"[^>]*>(.*?)<\/span[^>]*>#i', $response, $matches);
        return isset($matches[1]) ? (int) preg_replace("#\D#", "", html_entity_decode($matches[1])) : 0;
    }

    public static function yahoo($domain)
    {
        $url = sprintf(self::$yaUrl, $domain);
        if(!$response = HttpCurl::curl($url)) {
            return 0;
        }
        preg_match('#<span[^>]*>[^<]*results<\/span[^>]*>#i', $response, $result_tag);
        if(!isset($result_tag[0])) {
            return 0;
        }
        $tag = html_entity_decode($result_tag[0]);
        preg_match('#<span[^>]*>(.*)<\/span[^>]*>#i', $tag, $results);
        return isset($results[1]) ? (int) preg_replace("#\D#", "", $results[1]) : 0;
    }

    public static function yandex($domain) {
        if (!preg_match("#^www\.#i", $domain)) {
            $domain = "www.".$domain;
        }
        $url = sprintf(self::$yanUrl, $domain);
        if(!$response = HttpCurl::curl($url))
            return 0;
        preg_match('#<div class="header g-line">(.+?)</div>#is', $response, $matches);
        return isset($matches[1]) ? (int) preg_replace("#\D#", "", html_entity_decode(strip_tags($matches[1]))) : 0;
    }
}

