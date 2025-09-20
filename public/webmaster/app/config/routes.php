<?php
// Controller => URL name
return array(
    // internal routes
	'main' => '',
	'hide' => 'hide',
	'captcha'=>'captcha',
	// app routes
	//'alexa' => 'alexa-statistics',
	'social' => 'social-analytics',
	'catalog' => 'directory-listing-checker',
	'diagnostic' => 'website-diagnostic',
	'location' => 'domain-location',
	'search' => 'indexed-pages',
	'antispam' => 'antispam-protector',
	'metatags' => 'meta-tags-generator',
	'ogproperties' => 'og-properties-generator',
	'password' => 'password-generator',
	'hash' => 'md5-sha1-hashing',
	'duplicate' => 'duplicate-remover',
	'htmlencoder' => 'html-encoder',
	'timeconverter' => 'unix-time-converter',
	'textlength' => 'text-length-online',
	'whois' => 'whois-lookup',
	'dns' => 'dns-record-lookup',
	'headers' => 'http-headers',
    'backlinks' => 'backlinks',
    'suggest' => 'google-suggestion-tool',

    // commands
    'cmd_flush_cache'=>'cmd-flush-cache',
);