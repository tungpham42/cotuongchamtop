<?php
$domain = strtolower(urldecode(_v($_GET, 'domain')));
//$domain = 'google.lt';
$sCaptcha = _v($_SESSION, 'captcha');
$gCaptcha = _v($_GET, 'captcha');

if(ConfigFactory::load("captcha")->$controller) {
	if(empty($sCaptcha) or empty($gCaptcha) or $sCaptcha != $gCaptcha) {
		echo Response::run(array("error"=>t("form_error_captcha")));
		exit(0);
	}
}

$domain_ascii = (string) idn_to_ascii($domain);
if(!checkDomain($domain_ascii)) {
	echo Response::run(array("error"=>t("form_error_domain")));
	exit(0);
}

$ip = gethostbyname($domain_ascii);
$long = ip2long($ip);

if($long == -1 OR $long === FALSE) {
	echo Response::run(array("error"=>t("form_error_unreachable_domain", array("{host}"=>$domain))));
	exit(0);
}

if($table && $data = Model::select($table, $domain_ascii)) {
	if(time() - strtotime($data['Modified']) > ConfigFactory::load("cache")->$controller) {
		$data = call_user_func($controller."update", $domain_ascii, $ip);
	}
} else {
	$data = call_user_func($controller."insert", $domain_ascii, $ip);
}

$prepare_fn = $controller."PrepareData";
if(function_exists($prepare_fn)) {
    $prepare_fn($data);
}

ob_start();
include APP."tmpl".DS."service".DS.$controller."_result.php";
$content = ob_get_contents();
ob_end_clean();
echo Response::run(array("html"=>$content));
exit(0);