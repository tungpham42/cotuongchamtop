<?php
$fontname = APP."fonts".DS."inkswipe.ttf";

$fontsize = 20; // Font size
$width = 120;  // Captcha width
$height = 40; //Captcha height

$captcha = imagecreatetruecolor($width, $height);
$bg = imagecolorallocate($captcha, mt_rand(240, 255), mt_rand(240, 255), mt_rand(230,255));
$gridLine = imagecolorallocate($captcha, mt_rand(220, 240), mt_rand(220, 240),  mt_rand(220, 240));
$color = imagecolorallocate ($captcha, mt_rand(0, 50), mt_rand(0, 50), mt_rand(0, 50));

$string = getRandomString(5);
$_SESSION['captcha'] = $string;
imagefilledrectangle($captcha, 0, 0, $width, $height, $bg);
$bbox = createString($captcha, $fontsize, $fontname, $string, $width, $height, $color);
createNoise($width, $height, $captcha);
createGrid ($width, $height, $captcha, $gridLine);

header('Content-type: image/png');
imagepng($captcha);
imagedestroy($captcha);
exit(0);

//Create aligned center string
function createString($im, $fontsize, $fontname, $string, $width_img, $height_img, $black) {
	$degree = randomDegree();
	$bbox = imagettfbbox($fontsize, $degree, $fontname, $string);
	$right_text = $bbox[2];
	$left_text = $bbox[0];
	$width_text = $right_text - $left_text;
	$height_text = abs($bbox[7] - $bbox[1]);

	$text_x = $width_img/2.0 - $width_text/2.0;
	$text_y = $height_img/2.0 + $height_text/2.0;

	$ttftext = imagettftext($im, $fontsize, $degree, $text_x, $text_y, $black, $fontname, $string);
	return $ttftext;
}

function randomDegree() {
	$range = range(-1,1);
	$degree = $range[rand(0, count($range)-1)];
	return $degree;
}

function createNoise($w, $h, $im) {
	for ($i = 0; $i<= 500; $i++) {
		$noisePixels = imagecolorallocate($im , mt_rand(23, 250), mt_rand(23,250), mt_rand(26,230));
		imagesetpixel($im, mt_rand(0, $w), mt_rand(0, $h), $noisePixels);
	}
}


function createGrid($w, $h, $im, $gridLine) {
	$countLine = mt_rand(4, 10);
	for ($i = 1; $i <= $countLine; $i++) {
		imageline ($im, 0, ($h/$countLine)*$i*2, ($w/$countLine)*$i*2, 0, $gridLine);
		imageline ($im, $w, ($h/$countLine)*$i*2, $w-($w/$countLine*$i*2), 0, $gridLine);
	}
}

function getRandomString($length) {

	$chars = array ('a', 'b', 'c', 'e', 'o', 'p', 'x', 'l',
					'A', 'B', 'C', 'E', 'O', 'P', 'X', 'T');

	$pattern = getKeyboardPattern();

	switch($pattern) {
		case 'lower':
			$random_chars = range('a', 'z');
		break;

		case 'upper':
			$random_chars = range('A', 'Z');
		break;

		case 'both':
			$random_chars = array_merge(range('a','z'), range('A', 'Z'));
		break;

		default:
			$random_chars = range('a', 'z');
		break;
	}

	$random_chars = array_merge(range(1,9), $random_chars);

	$random_chars = array_diff($random_chars,$chars);

	shuffle($random_chars);

	$string = array_slice($random_chars, 0, $length);

	return implode('', $string);
}

function getKeyboardPattern() {
	$pattern = array('upper', 'lower', 'both');
	$randLayout = $pattern[rand(0, count($pattern)-1)];
	return $randLayout;
}