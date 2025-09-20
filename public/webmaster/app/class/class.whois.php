<?php
class Whois
{
	private static $whoisUrl = 'http://whomsy.com/api/%s';

	public static function get($url)
	{
		//return self::getFromApi($url);
		return self::getFromPHP($url);
	}

	public static function getFromApi($url) {
		$url = sprintf(self::$whoisUrl, $url);
		if(!$response = HttpCurl::curl($url))
			return NULL;
		if(!$whois = json_decode($response, true))
			return NULL;
		if($whois['type'] == 'success')
			return isset($whois['message']) ? $whois['message'] : NULL;
		else
			return NULL;
	}

	public static function getFromPHP($url) {
		$whois = new PHPWhois(
		    $url,
            APP."config".DS."whois.servers.php",
            APP."config".DS."whois.servers_charset.php",
            APP."config".DS."whois.params.php"
        );
		return $whois->query();
	}
}
