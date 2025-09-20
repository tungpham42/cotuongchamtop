<?php
/*
diagnose = array (
	'caution', 'warning', 'untested', 'safe'
);
*/
class Diagnostic
{
    private static $mcUrl = 'https://www.mcafee.com/threat-intelligence/site/default.aspx?url=%s';
    private static $glUrl = 'https://safebrowsing.googleapis.com/v4/threatMatches:find?key=%s';
    private static $norUrl = 'https://safeweb.norton.com/report/show?url=%s';
    private static $avgUrl = 'https://www.avgthreatlabs.com/us-en/website-safety-reports/domain/%s';

    public static function google($url)
    {
        $api_url = sprintf(self::$glUrl, ConfigFactory::load("app")->google_server_key);

        $data = json_encode(array(
            "client"=>array(
                "clientId"=>"PHP8 Developer. Codecanyon user",
                "clientVersion"=>"0.0.1",
            ),
            "threatInfo"=>array(
                "threatTypes"=>array("MALWARE", "SOCIAL_ENGINEERING", "UNWANTED_SOFTWARE", "POTENTIALLY_HARMFUL_APPLICATION"),
                "platformTypes"=>array("ALL_PLATFORMS"),
                "threatEntryTypes"=>array("URL"),
                "threatEntries"=>array(
                    array("url"=>$url)
                ),
            ),
        ));

        $curl = curl_init($api_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json", 'Content-Length: ' . strlen($data)));
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        if(!$raw = HttpCurl::curl_exec($curl)) {
            return 'untested';
        }

        $response = (array) @json_decode($raw, true);
        return isset($response['matches'][0]['threatType']) ? "caution" : "safe";
    }

    public static function mcafee($domain)
    {
        // McAfee doesn't allow to do automatic requests
        return 'untested';

        $state = array(
            'low'=>'safe',
            'unv'=>'untested',
            'med'=>'warning',
            'high'=>'caution',
        );

        $url = sprintf(self::$mcUrl, $domain);

        $url = "https://www.trustedsource.org/en/feedback/url";
        $headers = array(
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
            "Accept-Language: en-US,en;q=0.9,ru;q=0.8,lt;q=0.7",
            "Connection: keep-alive",
        );
        if(!$response = HttpCurl::curl($url, $headers)) {
            return 'untested';
        }
        /*
         * https://www.mcafee.com/threat-intelligence/site/default.aspx?url=hihanin.com
         *
         * https://www.mcafee.com/us/img/banners/threat/risk-meters/rm-webrep-low.png
         * https://www.mcafee.com/us/img/banners/threat/risk-meters/rm-webrep-unv.png
         * https://www.mcafee.com/us/img/banners/threat/risk-meters/rm-webrep-med.png
         * https://www.mcafee.com/us/img/banners/threat/risk-meters/rm-webrep-high.png
         */
        preg_match('#<img(?:[^>]*)src="/us/img/banners/threat/risk-meters/rm-webrep-(.*?)\.png"(?:[^>]*)>#is', $response, $matches);
        if(!isset($matches[1])) {
            return 'untested';
        } else {
            $key = strtolower($matches[1]);
            return isset($state[$key]) ? $state[$key] : 'untested';
        }
    }

    public static function avg($domain) {
        /**
         * 2018.04.22 AVG doesn't have online website status checker
         */
        return 'untested';

        $url = sprintf(self::$avgUrl, $domain);
        $diagnose = array(
            'green' => 'safe',
            'yellow' => 'warning',
            'orange' => 'warning',
            'red' => 'caution',
            'gray' => 'untested',
        );
        if(!$response = HttpCurl::curl($url))
            return 'untested';

        preg_match('#<div class="rating (.+?)"(.+?)>(.+?)</div>#is', $response, $matches);
        $d = isset($matches[1]) ? trim($matches[1]) : 'untested';
        return isset($diagnose[$d]) ? $diagnose[$d] : 'untested';
    }

    public static function norton($url) {
        $api_url = sprintf(self::$norUrl, $url);
        $diagnose = array(
            'icoSafe' => 'safe',
            'icoUntested' => 'untested',
            'icoWarning' => 'caution',
            'icoCaution' => 'warning',
            'icoNSecured' => 'safe',
        );
        if(!$response = HttpCurl::curl($api_url)) {
            return 'untested';
        }
        preg_match('#<img(?:[^>]*)class="big_clip (.*?)"(?:[^>]*)>#is', $response, $matches);
        $d = isset($matches[1]) ? trim($matches[1]) : 'untested';
        return isset($diagnose[$d]) ? $diagnose[$d] : 'untested';
    }
}
