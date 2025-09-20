<?php
function _v($a, $k, $d=null)
{
	return isset($a[$k]) ? $a[$k] : $d;
}

function base_url()
{
    static $base;
    if(!empty($base)) return $base;
    $protocol = isHttps() ? "https" : "http";
    $currentPath = $_SERVER['PHP_SELF'];
    $pathInfo = pathinfo($currentPath);
    $hostName = $_SERVER['HTTP_HOST'];
    $base = rtrim($protocol . "://" . $hostName . (isset($pathInfo['dirname']) ? $pathInfo['dirname'] : null), "/") . '/';
    return $base;
}

function create_url(array $params, $suffix = 'html')
{
	$r = implode("/", array_map("urlencode", $params)).(($suffix and ConfigFactory::load("app")->app_rewrite) ? '.'.$suffix : null);
	if(!ConfigFactory::load("app")->app_rewrite) {
        $r = '?r='.$r;
    }
	return base_url().$r;
}


function redirect($url, $suffix = 'html')
{
	$url = is_array($url) ? create_url($url, $suffix) : $url;
	header("Location: {$url}");
	exit(0);
}

function dd()
{
	$args = func_get_args();
	echo '<pre>';
	foreach($args as $arg)
		var_dump($arg);
	echo '</pre>';
	die();
}

function checkDomain($domain)
{
	return (bool)preg_match("#^[a-z\d-]{1,62}\.[a-z\d-]{1,62}(.[a-z\d-]{1,62})*$#i", $domain);
}

function isAjaxRequest()
{
	return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function alexaTime($num) {
	if($num==0) { return null; }
	if($num < 50) {
		return (100-$num)."% of sites are faster";
	} else {
		return $num."% of sites are slower";
	}
}

function isHttps() {
    if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
        return true;
    }
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
        return true;
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
        return true;
    }
    return false;
}

function controller_find($ctrl, $default = "404")
{
    $routes = ConfigFactory::load("routes");
    foreach($routes as $controller_id => $controller_regex) {
        $pattern = "#^{$controller_regex}$#is";
        if(preg_match($pattern, $ctrl)) {
            return $controller_id;
        }
    }
    return $default;
}

function controller_slug($ctrl, $not_found = "main")
{
    $routes = ConfigFactory::load("routes");
    $slug = $routes[$ctrl];
    return empty($slug) ? $routes[$not_found] : $slug;
}

function controller_url($ctrl, $suffix = "html", $not_found = "main")
{
    return create_url(array(controller_slug($ctrl, $not_found)), $suffix);
}

function controller_belongs_to_statistics($ctrl)
{
    return controller_in($ctrl, array(
        "alexa", "social", "catalog", "diagnostic", "location", "search", "backlinks",
    ));
}

function controller_belongs_to_seo($ctrl)
{
    return controller_in($ctrl, array(
        "suggest", "antispam", "metatags", "ogproperties", "password", "hash", "duplicate", "htmlencoder", "timeconverter", "textlength",
    ));
}

function controller_belongs_to_domainer($ctrl)
{
    return controller_in($ctrl, array(
        "whois", "dns", "headers",
    ));
}

function controller_in($ctrl, array $in)
{
    return in_array($ctrl, $in);
}

function set_meta_tags($controller)
{
    $title = t("{$controller}_meta_title", array(), false);
    $keywords = t("{$controller}_meta_keywords", array(), false);
    $description = t("{$controller}_meta_description", array(), false);


    $og = array();
    $og_title = t("{$controller}_og_title", array(), false);
    if(!empty($og_title)) {
        $og['title'] = $title;
    }
    $og_description = t("{$controller}_og_description", array(), false);
    if(!empty($og_description)) {
        $og['description'] = $description;
    }
    $og_image = t("{$controller}_og_image", array(), false);
    if(!empty($og_image)) {
        $og['image'] = $og_image;
    }

    HtmlHead::setTitle($title);
    HtmlHead::setKeywords($keywords);
    HtmlHead::setDescription($description);
    HtmlHead::setOg($og);
}

function t($phrase, array $params = array(), $default_phrase = true)
{
    $message = _v(ConfigFactory::load("messages"), $phrase, $default_phrase ? $phrase : "");
    return !empty($params) ? strtr($message, $params) : $message;
}

function f($number, $decimal=0)
{
    return number_format($number, $decimal, ConfigFactory::load("app")->app_dec_point, ConfigFactory::load("app")->app_thousands_sep);
}

function navbar_brand()
{
    $nav_name = ConfigFactory::load("app")->app_name;
    $nav_icon = ConfigFactory::load("app")->app_nav_icon;

    if(!empty($nav_icon)) {
        return '<img src="'.$nav_icon.'" alt="'.$nav_name.'" width="30" height="30"/>';
    } else {
        return $nav_name;
    }
}

function navbar_active_class($condition, $class = "active")
{
    return $condition ? " {$class}" : "";
}

function escape_html($text)
{
    return htmlentities($text, ENT_QUOTES);
}

function remove_non_int_characters($string)
{
    return preg_replace("/[^\d+]/is", "", $string);
}

function sess_init()
{
    $config = ConfigFactory::load("app");
    $data = session_get_cookie_params();
    $path = $config['app_cookie_path']."; samesite=". $config['app_cookie_samesite'];
    session_name($config['app_session_name']);
    session_set_cookie_params(
        $data['lifetime'],
        $path,
        $data['domain'],
        $config['app_cookie_secure'],
        true
    );
    session_start();
}

function cookie_consent_path()
{
    $conf = ConfigFactory::load("app");
    $path = $conf['app_cookie_path'].";samesite=".$conf['app_cookie_samesite'];
    if($conf['app_cookie_secure']) {
        $path .= ";secure";
    }
    return $path;
}

function curl_get_home_domain_url($domain)
{
    $url = "http://{$domain}";
    $ch = HttpCurl::ch(curl_init($url));
    if(false === $ch) {
        return $url;
    }
    curl_exec($ch);
    if(curl_errno($ch)) {
        return $url;
    }
    $final_url = curl_get_final_url(curl_getinfo($ch), $url);
    return url_get_scheme_host($final_url, $url);
}

function curl_get_final_url($curl_info, $default)
{
    if(false === $curl_info) {
        return $default;
    }
    if(!empty($curl_info['redirect_url'])) {
        return $curl_info['redirect_url'];
    }
    return _v($curl_info, "url", $default);
}

function url_get_scheme_host($url, $default)
{
    $parsed = parse_url($url);
    if(false === $parsed) {
        return $default;
    }
    if(!isset($parsed['scheme'], $parsed['host'])) {
        return $default;
    }
    return $parsed['scheme']."://".$parsed['host'].'/';
}

function escape_single_quote($str)
{
    return addcslashes($str, "'");
}

function escape_double_quote($str)
{
    return addcslashes($str, '"');
}

function html_special_chars($str)
{
    return htmlspecialchars($str, ENT_QUOTES, "UTF-8");
}

function header_404()
{
    return _v($_SERVER, "SERVER_PROTOCOL",  "HTTP/1.1")." 404 Not Found";
}