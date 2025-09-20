<?php
Class HttpCurl
{
    protected static $cacheFolder;

    public static function setCacheFolder($cacheFolder) {
        self::$cacheFolder = $cacheFolder;
    }

    public static function curl($url, array $headers = array(), $cookie = false) {
        $ch = curl_init($url);
        if($cookie) {
            $cookie = self::$cacheFolder.DS."cookie_{$cookie}.txt";
        }
        $html = self::curl_exec($ch, $headers, $cookie);
        curl_close($ch);
        return $html;
    }

    public static function curl_exec($ch, $headers=array(), $cookie = false, & $maxredirect = null)
    {
        return curl_exec(self::ch($ch, $headers, $cookie, $maxredirect));
    }

    public static function ch($ch, $headers=array(), $cookie = false, &$maxredirect = null) {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);

        if($cookie) {
            curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie);
            curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookie);
        }

        if(!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if(isset($headers['user_agent'])) {
            $user_agent = $headers['user_agent'];
            unset($headers['user_agent']);
        } else {
            $user_agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.41 Safari/537.36";
        }


        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent );

        $mr = $maxredirect === null ? 5 : intval($maxredirect);
        if (ini_get('open_basedir') == '' && (ini_get('safe_mode') == 'Off' || ini_get('safe_mode')=='')) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $mr > 0);
            curl_setopt($ch, CURLOPT_MAXREDIRS, $mr);
        } else {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            $original_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            $parsed = parse_url($original_url);
            if(!$parsed) {
                return false;
            }
            $scheme = isset($parsed['scheme']) ? $parsed['scheme'] : '';
            $host = isset($parsed['host']) ? $parsed['host'] : '';

            if ($mr > 0)
            {
                $newurl = $original_url;
                $rch = curl_copy_handle($ch);

                curl_setopt($rch, CURLOPT_HEADER, true);
                curl_setopt($rch, CURLOPT_NOBODY, true);
                curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
                curl_setopt($rch, CURLOPT_RETURNTRANSFER, true);
                do
                {
                    curl_setopt($rch, CURLOPT_URL, $newurl);
                    $header = curl_exec($rch);
                    if (curl_errno($rch)) {
                        $code = 0;
                    } else {
                        $code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
                        if (in_array($code, array(301, 302, 307, 308))) {
                            preg_match('/Location:(.*?)\n/i', $header, $matches);
                            $newurl = trim(array_pop($matches));

                            if(!$parsed = parse_url($newurl)) {
                                return false;
                            }

                            if(!isset($parsed['scheme'])) {
                                $parsed['scheme'] = $scheme;
                            } else {
                                $scheme = $parsed['scheme'];
                            }

                            if(!isset($parsed['host'])) {
                                $parsed['host'] = $host;
                            } else {
                                $host = $parsed['host'];
                            }
                            $newurl = self::unparse_http_url($parsed);
                        } else {
                            $code = 0;
                        }
                    }
                } while ($code && --$mr);
                curl_close($rch);

                if (!$mr)
                {
                    if ($maxredirect === null)
                        return false;
                    else
                        $maxredirect = 0;

                    return false;
                }
                curl_setopt($ch, CURLOPT_URL, $newurl);
            }
        }
        return $ch;
    }

    public static function unparse_http_url(array $parsed) {
        if(!isset($parsed['host'])) {
            return false;
        }
        $url = isset($parsed['scheme']) ? $parsed['scheme']."://" : "http://";
        if(isset($parsed['user'])) {
            $url .= $parsed['user'];
            if(isset($parsed['pass'])) {
                $url .= ":".$parsed['pass'];
            }
            $url .= "@".$parsed['host'];
        } else {
            $url .= $parsed['host'];
        }

        if(isset($parsed['port'])) {
            $url .= ":".$parsed['port'];
        }

        if(isset($parsed['path'])) {
            $url .= $parsed['path'];
        }
        if(isset($parsed['query'])) {
            $url .= "?".$parsed['query'];
        }
        if(isset($parsed['fragment'])) {
            $url .= "#".$parsed['fragment'];
        }
        return $url;
    }
}