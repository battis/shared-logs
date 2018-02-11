<?php
/** ObjectException */

namespace Battis\SharedLogs\Exceptions;

use Battis\SharedLogs\Exception;

/**
 * Exceptions thrown by Object instances
 *
 * @author Seth Battis <seth@battis.net>
 */
class ObjectException extends Exception
{
    /** Error code indicating that the object did not receive a database record */
    const MISSING_DATABASE_RECORD = 100;
    const NESTED_RECORD_MISMATCH = 101;
}
