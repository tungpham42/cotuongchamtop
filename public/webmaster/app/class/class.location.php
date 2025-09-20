<?php
class Location
{
    //private static $locApi = "http://api.hostip.info/get_json.php?ip=%s&position=true";
    private static $freeGeoIpNetApi = "http://freegeoip.net/json/%s";
    private static $telizeComApi = "http://www.telize.com/geoip/%s";
    private static $nekudoApi = 'http://geoip.nekudo.com/api/%s';
    private static $ipApiApi = 'http://ip-api.com/json/%s';

    public static function get($domain, $ip)
    {
        return self::getFromIpApi($ip);
        //return self::getFromNekudo($ip);
        //return self::getFromFreeGeoIpNet($domain);
        // return self::getFromTelizeCom($ip);
    }



    protected static  function getFromFreeGeoIpNet($ip) {
        $pattern = array(
            'city'=>'Unknown',
            'region_name'=>'Unknown',
            'ip'=>'Unknown',
            'longitude'=>0,
            'country_name'=>'Unknown',
            'country_code'=>'XX',
            'latitude'=>0,
        );
        $url = sprintf(self::$freeGeoIpNetApi, $ip);
        if(!$response = HttpCurl::curl($url)) {
            return $pattern;
        }
        if(!$json = json_decode($response, true)) {
            return $pattern;
        }

        return array(
            'city'=> isset($json['city']) ? $json['city'] : 'Unknown',
            'region_name'=>isset($json['region_name']) ? $json['region_name'] : 'Unknown',
            'ip'=>isset($json['ip']) ? $json['ip'] : 'Unknown',
            'longitude'=>isset($json['longitude']) ? $json['longitude'] : 0,
            'country_name'=>isset($json['country_name']) ? $json['country_name'] : 'Unknown',
            'country_code'=>(isset($json['country_code']) AND strlen($json['country_code'])==2) ? $json['country_code'] : 'XX',
            'latitude'=>isset($json['latitude']) ? $json['latitude'] : 0,
        );
    }

    protected static function getFromTelizeCom($ip) {
        $pattern = array(
            'city'=>'Unknown',
            'region_name'=>'Unknown',
            'ip'=>'Unknown',
            'longitude'=>0,
            'country_name'=>'Unknown',
            'country_code'=>'XX',
            'latitude'=>0,
        );
        $url = sprintf(self::$telizeComApi, $ip);
        if(!$response = HttpCurl::curl($url)) {
            return $pattern;
        }
        if(!$json = json_decode($response, true)) {
            return $pattern;
        }

        return array(
            'city'=> isset($json['city']) ? $json['city'] : 'Unknown',
            'region_name'=>isset($json['region']) ? $json['region'] : 'Unknown',
            'ip'=>isset($json['ip']) ? $json['ip'] : 'Unknown',
            'longitude'=>isset($json['longitude']) ? $json['longitude'] : 0,
            'country_name'=>isset($json['country']) ? $json['country'] : 'Unknown',
            'country_code'=>(isset($json['country_code']) AND strlen($json['country_code'])==2) ? $json['country_code'] : 'XX',
            'latitude'=>isset($json['latitude']) ? $json['latitude'] : 0,
        );
    }

    protected static function getFromNekudo($ip) {
        $pattern = array(
            'city'=>'Unknown',
            'region_name'=>'Unknown',
            'ip'=>'Unknown',
            'longitude'=>0,
            'country_name'=>'Unknown',
            'country_code'=>'XX',
            'latitude'=>0,
        );
        $url = sprintf(self::$nekudoApi, $ip);
        if(!$response = HttpCurl::curl($url)) {
            return $pattern;
        }
        if(!$json = json_decode($response, true)) {
            return $pattern;
        }

        return array(
            'city'=> isset($json['city']) ? $json['city'] : 'Unknown',
            'region_name'=>'Unknown',
            'ip'=>isset($json['ip']) ? $json['ip'] : 'Unknown',
            'longitude'=>isset($json['location']['longitude']) ? $json['location']['longitude'] : 0,
            'country_name'=>isset($json['country']['name']) ? $json['country']['name'] : 'Unknown',
            'country_code'=>(isset($json['country']['code']) AND strlen($json['country']['code'])==2) ? $json['country']['code'] : 'XX',
            'latitude'=>isset($json['location']['latitude']) ? $json['location']['latitude'] : 0,
        );
    }

    protected static function getFromIpApi($ip) {
        $pattern = array(
            'city'=>'Unknown',
            'region_name'=>'Unknown',
            'ip'=>'Unknown',
            'longitude'=>0,
            'country_name'=>'Unknown',
            'country_code'=>'XX',
            'latitude'=>0,
        );
        $url = sprintf(self::$ipApiApi, $ip);
        if(!$response = HttpCurl::curl($url)) {
            return $pattern;
        }
        if(!$json = json_decode($response, true)) {
            return $pattern;
        }

        return array(
            'city'=> isset($json['city']) ? $json['city'] : 'Unknown',
            'region_name'=>isset($json['regionName']) ? $json['regionName'] : 'Unknown',
            'ip'=>$ip,
            'longitude'=>isset($json['lon']) ? $json['lon'] : 0,
            'country_name'=>isset($json['country']) ? $json['country'] : 'Unknown',
            'country_code'=>(isset($json['countryCode']) AND strlen($json['countryCode'])==2) ? $json['countryCode'] : 'XX',
            'latitude'=>isset($json['lat']) ? $json['lat'] : 0,
        );
    }
}