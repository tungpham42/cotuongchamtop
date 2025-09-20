<?php
function alexaUpdate($domain) {
	$data = SearchCatalog::alexa($domain);
	$data['Modified'] = date("Y-m-d H:i:s");
	$data['data'] = @json_encode($data['data']);
	Model::update("alexa", $data, $domain);
	return $data;
}

function alexaInsert($domain) {
	$data = SearchCatalog::alexa($domain);
	$data['Domain'] = $domain;
	$data['Added'] = date("Y-m-d H:i:s");
	$data['Modified'] = date("Y-m-d H:i:s");
    $data['data'] = @json_encode($data['data']);
	Model::insert("alexa", $data);
	return $data;
}

function alexaPrepareData(& $data)
{
    $custom = @json_decode($data['data'], true);
    $data['delta_direction'] = _v($custom, 'delta_direction');
    $data['delta'] = _v($custom, 'delta', 0);
    $data['similar_sites'] = _v($custom, 'similar_sites', array());
    $data['related_keywords'] = _v($custom, 'related_keywords', array());
}

if(isAjaxRequest()) {
	$table = $controller;
	include APP."controllers".DS."process_ajax.php";
}
include APP."tmpl".DS."service".DS."alexa.php";