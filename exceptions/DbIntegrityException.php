<?php
namespace exceptions;

/**
 * Exception represents an exception that is caused by violation of DB constraints.
 *
 */
class DbIntegrityException extends DbException
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Integrity constraint violation';
    }
}
