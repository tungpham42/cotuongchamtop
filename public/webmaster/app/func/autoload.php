<?php
spl_autoload_register('autoload');

function autoload($className)
{
	$file = ROOT.'app'.DS.'class'.DS.'class.'.strtolower($className).'.php';
	if(file_exists($file)) {
        include $file;
    }
}