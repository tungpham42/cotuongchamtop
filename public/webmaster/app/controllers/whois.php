<?php
function whoisUpdate($domain) {
	$data = array(
		'Whois' => Whois::get($domain),
		'Modified' => date("Y-m-d H:i:s"),
	);
	Model::update("whois", $data, $domain);
	return $data;
}

function whoisInsert($domain) {
	$data = array(
		'Domain' => $domain,
		'Added' => date("Y-m-d H:i:s"),
		'Modified' => date("Y-m-d H:i:s"),
		'Whois' => Whois::get($domain),
	);
	Model::insert("whois", $data);
	return $data;
}

if(isAjaxRequest()) {
	$table = $controller;
	include APP."controllers".DS."process_ajax.php";
}
include APP."tmpl".DS."service".DS."whois.php";