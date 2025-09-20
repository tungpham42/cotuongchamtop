<?php
error_reporting(E_ALL & ~(E_NOTICE | E_DEPRECATED | E_STRICT));

// Set UTF-8 encoding
mb_internal_encoding('UTF-8');
setlocale(LC_ALL, 'en_US.UTF-8');

define("DS", DIRECTORY_SEPARATOR);
define("ROOT", dirname(__FILE__).DS);
define("APP", ROOT."app".DS);

// include autoload and some helper functions
include ROOT.'app'.DS.'func'.DS.'autoload.php';
include ROOT.'app'.DS.'func'.DS.'helpers.php';

sess_init();

// Get the request string
$r = str_replace(array(".html", ".php"), "", trim(_v($_GET, 'r'), "/"));
$controller = controller_find($r);
$amp = ConfigFactory::load("app")->app_rewrite ? "?" : "&";

HttpCurl::setCacheFolder(ConfigFactory::load("app")->app_curl_cookie_cache);

ob_start();

if("404" === $controller) {
    header(header_404(), true);
}
require APP."controllers".DS.$controller.".php";

set_meta_tags($controller);

$content = ob_get_contents();
// clean buffer
ob_end_clean();

// Output result to main template
include APP.'tmpl'.DS.'template.php';