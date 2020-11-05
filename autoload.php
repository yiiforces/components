<?php
define('DS', DIRECTORY_SEPARATOR);

// tmp dev autoload without composer
spl_autoload_register(function($className) {

	$file = sprintf('%s/%s.php', __DIR__ . DS, $className);
	$file = str_replace('\\', DS, $file);
	$file = str_replace('//', '/', $file);

	if(file_exists($file))
		return include $file;
});
