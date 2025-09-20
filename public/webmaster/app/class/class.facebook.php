<?php
class Facebook
{
    private static $ins;

    private $accessToken;

    private function __construct() {
        $config = ConfigFactory::load("app");

        $appId = $config['facebook_app_id'];
        $appSecret = $config['facebook_app_secret'];
        $this->accessToken = "{$appId}|{$appSecret}";
    }

    public static function ins() {
        if(!self::$ins) {
            self::$ins = new self();
        }
        return self::$ins;
    }

    public function getLikes($url) {
        $params = array(
            "id"=>$url,
            "fields"=>'engagement',
            "access_token"=>$this->accessToken,
        );
        return @json_decode(HttpCurl::curl($this->buildUrl($params)), true);
    }

    private function buildUrl($params) {
        $appUrl = "https://graph.facebook.com/v10.0/";
        $requestUrl = $appUrl."?".http_build_query($params);
        return $requestUrl;
    }
}