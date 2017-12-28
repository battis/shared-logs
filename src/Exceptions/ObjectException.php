<?php

namespace Battis\SharedLogs\Exceptions;

use Battis\SharedLogs\Exception;

/**
 * Exceptions thrown by Objects
 *
 * @author Seth Battis <seth@battis.net>
 */
class ObjectException extends Exception
{
    const MISSING_ARGUMENTS = 100;
}