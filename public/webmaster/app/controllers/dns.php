<?php
function dnsInsert($domain) {
	$dns = @dns_get_record($domain, ConfigFactory::load("app")->dns_type);
	if(!is_array($dns))
		$dns = array();

	return array(
		'dns' => $dns,
	);
}

if(isAjaxRequest()) {
	$table = false;
	include APP."controllers".DS."process_ajax.php";
}

include APP."tmpl".DS."service".DS."dns.php";