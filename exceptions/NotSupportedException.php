<?php
namespace exceptions;
/**
 * NotSupportedException represents an exception caused by accessing features that are not supported.
 */
class NotSupportedException extends Exception
{
    public function getName()
    {
        return 'Not Supported';
    }
}
