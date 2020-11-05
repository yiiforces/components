<?php
namespace exceptions;

class InvalidConfigException extends \Exception
{
	public function getName()
	{
		return 'Invalid Configuration';
	}
}
