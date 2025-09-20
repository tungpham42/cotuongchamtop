<?php
function diagnosticUpdate($domain) {
    $url = curl_get_home_domain_url($domain);
    $data = array();
	$data['norton'] = Diagnostic::norton($url);
    $data['google'] = Diagnostic::google($url);

	$data['avg'] = Diagnostic::avg($domain);
	$data['mcafee'] = Diagnostic::mcafee($domain);

	$data['Modified'] = date("Y-m-d H:i:s");
	Model::update("diagnostic", $data, $domain);
	return $data;
}

function diagnosticInsert($domain) {
    $url = curl_get_home_domain_url($domain);
    $data = array();
    $data['norton'] = Diagnostic::norton($url);
    $data['google'] = Diagnostic::google($url);

	$data['avg'] = Diagnostic::avg($domain);
	$data['mcafee'] = Diagnostic::mcafee($domain);

	$data['Domain'] = $domain;
	$data['Added'] = date("Y-m-d H:i:s");
	$data['Modified'] = date("Y-m-d H:i:s");
	Model::insert("diagnostic", $data);
	return $data;
}

if(isAjaxRequest()) {
	$table = $controller;
	include APP."controllers".DS."process_ajax.php";
}
include APP."tmpl".DS."service".DS."diagnostic.php";