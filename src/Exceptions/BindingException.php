<?php

namespace Battis\SharedLogs\Exceptions;

use Battis\SharedLogs\Exception;

class BindingException extends Exception
{
    const MISSING_DATABASE_CONNECTOR = 1;
}