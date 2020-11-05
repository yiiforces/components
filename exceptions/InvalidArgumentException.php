<?php
namespace exceptions;

class InvalidArgumentException extends InvalidParamException
{
    public function getName()
    {
        return 'Invalid Argument';
    }
}
