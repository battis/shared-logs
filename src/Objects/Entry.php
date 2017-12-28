<?php

namespace Battis\SharedLogs\Objects;

use Battis\SharedLogs\Object;

class Entry extends Object
{
    const SUPPRESS_LOG = false;
    const SUPPRESS_USER = false;

    const ID = 'entry_id';

    public function __construct($databaseRow, $log = self::SUPPRESS_LOG, $user = self::SUPPRESS_USER)
    {
        parent::__construct($databaseRow);

        if ($log instanceof Log) {
            $this->log = $log;
        }
        if ($user instanceof User) {
            $this->user = $user;
        }
    }
}