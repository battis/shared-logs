<?php

namespace Battis\SharedLogs\Objects;

use Battis\SharedLogs\Object;

class Device extends Object
{
    const SUPPRESS_LOGS = false;

    const ID = 'device_id';

    public function __construct($databaseRow, $logs = self::SUPPRESS_LOGS)
    {
        parent::__construct($databaseRow);

        if (is_array($logs)) {
            $this->logs = array_filter($logs, function ($elt) {
                return $elt instanceof Log;
            });
        }
    }
}