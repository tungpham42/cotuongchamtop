<?php
function get_headers_from_curl_response($response) {
	$headers = array();
	$header_text = substr($response, 0, strpos($response, "\r\n\r\n"));
	foreach (explode("\r\n", $header_text) as $i => $line)
		if ($i === 0) {
            $headers['Http Code'] = $line;
        } else {
			list ($key, $value) = explode(': ', $line);
			$headers[$key] = $value;
		}
	return $headers;
}

function headersInsert($domain) {
    $url = curl_get_home_domain_url($domain);

	$user_agent = _v($_GET, 'user-agent');
	$_http = array(
		'1.1' => CURL_HTTP_VERSION_1_1,
		'1.0' => CURL_HTTP_VERSION_1_0,
	);
	$http = in_array(_v($_GET, 'HTTP'), $_http) ? $_http[_v($_GET, 'HTTP')] : $_http['1.1'];


	$config = ConfigFactory::load('browser');
	$user_agent = $config->$user_agent === NULL ? $config->firstValue() : $config->$user_agent;
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_HTTP_VERSION, $http);
	
	if(_v($_GET, 'request') == 'POST') {
		curl_setopt($ch, CURLOPT_POST , 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('bot'=>'PHP8 Developer. CodeCanyon User'));
	}
		
	if(_v($_GET, 'gzip')) {
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
    }

	$buffer = curl_exec($ch);
	$curl_info = curl_getinfo($ch);
	curl_close($ch);
	$header_size = $curl_info['header_size'];
	$headers = get_headers_from_curl_response(mb_substr($buffer, 0, $header_size));
	$body = mb_substr($buffer, $header_size, mb_strlen($buffer));

	return array(
		'size' => sprintf("%.02f", $curl_info['size_download'] / 1024),
		'body' => $body,
		'headers' => $headers,
	);
}

if(isAjaxRequest()) {
	$table = false;
	include APP."controllers".DS."process_ajax.php";
}

include APP."tmpl".DS."service".DS."headers.php";