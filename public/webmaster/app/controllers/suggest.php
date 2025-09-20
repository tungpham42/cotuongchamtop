<?php
if(isAjaxRequest()) {
    $sCaptcha = _v($_SESSION, 'captcha');
    $gCaptcha = _v($_GET, 'captcha');
    $keyword = _v($_GET, 'keyword');
    $lg = substr(_v($_GET, 'lg'), 0, 2);

    if(ConfigFactory::load("captcha")->$controller) {
        if(empty($sCaptcha) or empty($gCaptcha) or $sCaptcha != $gCaptcha) {
            echo Response::run(array("error"=>t("form_error_captcha")));
            exit(0);
        }
    }

    if(empty($keyword)) {
        echo Response::run(array("error"=>t("form_error_keywords")));
        exit(0);
    }
    if(empty($lg)) {
        echo Response::run(array("error"=>t("form_error_language")));
        exit(0);
    }

    $suggestions = Suggest::google($keyword, $lg);
    ob_start();
    include APP."tmpl".DS."service".DS."suggest_result.php";
    $content = ob_get_contents();
    ob_end_clean();
    echo Response::run(array("html"=>$content));
    exit(0);
}

include APP."tmpl".DS."service".DS."suggest.php";