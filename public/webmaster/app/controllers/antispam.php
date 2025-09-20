<?php
if(isAjaxRequest()) {
    $config = ConfigFactory::load("antispam");
    $sCaptcha = _v($_SESSION, 'captcha');
    $gCaptcha = _v($_GET, 'captcha');

    if(ConfigFactory::load("captcha")->$controller) {
        if(empty($sCaptcha) or empty($gCaptcha) or $sCaptcha != $gCaptcha) {
            echo Response::run(array("error"=>t("form_error_captcha")));
            exit(0);
        }
    }
	$fontsize = _v($_GET, "font-size");
	$text = _v($_GET, "text");
	$bg = _v($_GET, "background-color");
	$textbg = _v($_GET, "text-color");
	$family = _v($_GET, "font-family");
	
	if(mb_strlen($text) == 0) {
		echo Response::run(array("error"=>t("form_error_text")));
		exit(0);	
	}
	if(mb_strlen($text) > ConfigFactory::load("antispam")->max_text_len) {
        echo Response::run(array("error"=>t("form_error_exceed_length", array(
            "{length}"=>ConfigFactory::load("antispam")->max_text_len,
            "{attribute}"=>t("antispam_text"),
        ))));
        exit(0);
    }

	$crypt = new Crypt(ConfigFactory::load('app')->app_secure_key);
	$e = $crypt->encode($text);	

	$antispam_img = controller_url("hide", "").
		$amp.'enc='.rawurlencode($e).
		'&size='.rawurlencode($fontsize).
		'&bg='.rawurlencode($bg).
		'&textbg='.rawurlencode($textbg).
		'&family='.rawurlencode($family)
		;
	
	ob_start();
	include APP."tmpl".DS."service".DS."antispam_result.php";
	$content = ob_get_contents();
	ob_end_clean();
	echo Response::run(array("html"=>$content));
	exit(0);	
}

HtmlHead::setCss(array("colorpicker" => base_url()."static/css/colorpicker.css"));
HtmlHead::setJs(array("colorpicker" => base_url()."static/js/colorpicker.js"));

include APP."tmpl".DS."service".DS."antispam.php";