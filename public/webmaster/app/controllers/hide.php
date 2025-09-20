<?php
function first(array $array) {
	return current(array_slice($array, 0, 1));
}

function last(array $array) {
	return end($array);
}

//HEX to RGB converter
function hexrgb($hexstr) {
	$int = hexdec($hexstr);
	return array("red" => 0xFF & ($int >> 0x10), "green" => 0xFF & ($int >> 0x8), "blue" => 0xFF & $int);
}

$enc = _v($_GET, 'enc');
if(empty($enc)) {
    exit(0);
}

$crypt = new Crypt(ConfigFactory::load("app")->app_secure_key);
$text = $crypt->decode($enc);
if(false === $text) {
    exit(0);
}
$font_size = _v($_GET, 'size');
$font_family = _v($_GET, 'family');
$text_color = hexrgb(_v($_GET, 'textbg'));
$bg_color = hexrgb(_v($_GET, 'bg'));
$config = ConfigFactory::load("antispam");


// Initializing variables

if($font_size > last($config->font_size) or $font_size < first($config->font_size)) {
	$font_size = first($config->font_size); 
}
if(!in_array($font_family, $config->font_family)) {
    $font_family = first($config->font_family);
}

$font_file = ROOT.'app'.DS.'fonts'.DS.'hide'.DS.$font_family.'.ttf';

// Retrieve bounding box:
$type_space = imagettfbbox($font_size, 0, $font_file, $text);

// Padding between lines
$padding = $font_size * 0.6;

// Determine image width and height, 10 pixels are added for 5 pixels padding:
$image_width = abs($type_space[4] - $type_space[0]) + $font_size + $padding;
$image_height = abs($type_space[5] - $type_space[1]) + $font_size;

//echo $image_width. ' '. $image_height;
//die();

// Create image:
$image = imagecreatetruecolor($image_width, $image_height);

// Allocate text and background colors (RGB format):
$text_color = imagecolorallocate($image, $text_color['red'], $text_color['green'], $text_color['blue']);
$bg_color = imagecolorallocate($image, $bg_color['red'], $bg_color['green'], $bg_color['blue']);

// Fill image:
imagefill($image, 0, 0, $bg_color);

// Fix starting x and y coordinates for the text:
$x = $padding; // X-axis padding
$y = $font_size + $padding; // So that the text is vertically centered.

// Break it up into pieces
$lines = explode("\n", $text);

// Add TrueType text to image:
foreach ($lines as $line)
{
    imagettftext($image, $font_size, 0, $x, $y, $text_color, $font_file, $line);
    // Increment Y so the next line is below the previous line
		$y += $font_size + $padding;
}

// Generate and send image to browser:
header('Content-type: image/png');
imagepng($image);

// Destroy image in memory to free-up resources:
imagedestroy($image);

exit(0);