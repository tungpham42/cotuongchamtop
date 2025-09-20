<?php
return array (
    // Basic settings
	'app_rewrite'=>true,
    'app_name'=>'Webmaster Tools',
    'app_secure_key'=>'6vKqH8gGVveVZnxoLxO0kJArZlHcTF7A',
    'app_nav_icon'=>'',
    'app_command_key'=>'',
    "app_dec_point"=>".",
    "app_thousands_sep"=>",",
    'app_html_lang'=>'en',
    'app_curl_cookie_cache'=>APP."cache",
    'app_domain_placeholder'=>'php8developer.com',
    'app_banner_top'=>'',
    'app_banner_bottom'=>'',
    'app_html_head'=>'',
    'app_html_footer'=>'<p class="mt-3">Developed by <strong><a href="https://php8developer.com">PHP8 Developer</a></strong></p>',

    // DB Settings
    'db_host'=>'localhost',
    'db_username'=>'homestead',
    'db_passwd'=>'secret',
    'db_name'=>'webmaster_toolkit',
    'db_port'=>'3306',
    'db_default_socket'=>ini_get("mysqli.default_socket"),
    'db_charset'=>'utf8mb4',
    'db_table_prefix'=>'wt_',

    // Session settings
    'app_session_name'=>'_WT_SESSID',

    // Cookie settings
    'app_cookie_secure'=>false,
    'app_cookie_samesite'=>'Lax',
    'app_cookie_path'=>'/',

    // Cookie Consent settings
    'app_cookie_consent_enable'=>true,
    'app_cookie_consent_theme'=>"light-floating", // dark-top, dark-floating, dark-bottom, light-floating, light-top, light-bottom
    'app_cookie_consent_link'=>"https://www.google.com/intl/en/policies/privacy/partners/", // Leave empty to hide link (Learn more)
    'app_cookie_consent_expiry_days'=>365,

    // Facebook settings
    'facebook_app_id'=>'',
    'facebook_app_secret'=>'',

    // Google settings
    'google_server_key'=>'',
    'google_browser_key'=>'',

    // DNS controller
    'dns_type'=>DNS_ANY,
);
