<?php
namespace exceptions;

class UnknownPropertyException extends \Exception
{
	public function getName()
	{
		return 'Unknown Property';
	}
}
