<?php
function socialUpdate($domain) {
    $url = curl_get_home_domain_url($domain);
    $data = array();
	$facebook = Social::facebook($url);
	$data['pinterest'] = Social::pinterest($url);
	$data = array_merge($data, $facebook);
	$data['Modified'] = date("Y-m-d H:i:s");
	Model::update("social", $data, $domain);
	return $data;
}

function socialInsert($domain) {
    $url = curl_get_home_domain_url($domain);
    $data = array();
    $facebook = Social::facebook($url);
    $data['pinterest'] = Social::pinterest($url);
    $data = array_merge($data, $facebook);
    $data['Domain'] = $domain;
	$data['Added'] = date("Y-m-d H:i:s");
	$data['Modified'] = date("Y-m-d H:i:s");
	Model::insert("social", $data);
	return $data;
}

if(isAjaxRequest()) {
	$table = $controller;
	include APP."controllers".DS."process_ajax.php";
}
include APP."tmpl".DS."service".DS."social.php";