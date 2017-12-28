<?php
/** BindingException */

namespace Battis\SharedLogs\Exceptions;

use Battis\SharedLogs\Exception;

/**
 * Exceptions thrown by Binding instances
 *
 * @author Seth Battis <seth@battis.net>
 */
class BindingException extends Exception
{
    /** Error code indicating that the binding did not receive a valid database connector */
    const MISSING_DATABASE_CONNECTOR = 1;
}