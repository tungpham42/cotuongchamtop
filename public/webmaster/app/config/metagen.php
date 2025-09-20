<?php
return array(
	'robots' => array(
		'', // Default value
		'index, follow',
		'index, nofollow',
		'noindex, follow',
		'noindex, nofollow',
	),
	'charset' => array(
		'', // Default value
		'GB2312',
		'US-ASCII',
		'ISO-8859-1',
		'ISO-8859-2',
		'ISO-8859-3',
		'ISO-8859-4',
		'ISO-8859-5',
		'ISO-8859-6',
		'ISO-8859-7',
		'ISO-8859-8',
		'ISO-8859-9',
		'ISO-2022-JP',
		'ISO-2022-JP-2',
		'ISO-2022-KR',
		'UTF-8',
	),
	'cache' => array(
		'', // Default value
		'cache',
		'no-cache',
	),
	'language' => array(
		'' => '', // Default value
		'en' => 'English',
		'fr' => 'French',
		'ch' => 'Chinese',
		'de' => 'German',
		'es' => 'Spanish',
		'ja' => 'Japanese',
		'ru' => 'Russian',
	),
	'revisit' => array(
		'days', 'month',
	),
	// More formats: http://php.net/manual/en/function.date.php
	'expires' => date("D").','. date('d M Y H:i:s'). ' GMT',
);