<?php

namespace Battis\SharedLogs\Objects;

use Battis\SharedLogs\Object;

class Log extends Object
{
    const SUPPRESS_DEVICE = false;
    const SUPPRESS_ENTRIES = false;

    const ID = 'log_id';

    public function __construct($databaseRow, $device = self::SUPPRESS_DEVICE, $entries = self::SUPPRESS_ENTRIES)
    {
        parent::__construct($databaseRow);

        if ($device instanceof Device) {
            $this->device = $device;
        }
        if (is_array($entries)) {
            $this->entries = array_filter($entries, function ($elt) {
                return $elt instanceof Entry;
            });
        }
    }
}