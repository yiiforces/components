<?php

// tmp dev autoload without composer
spl_autoload_register(function($className) {

	$file = sprintf('%s/%s.php', __DIR__ . DIRECTORY_SEPARATOR, $className);
	$file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
	$file = str_replace('//', '/', $file);

	if(file_exists($file))
		return include $file;
});
