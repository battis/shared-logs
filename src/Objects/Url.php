<?php

namespace Battis\SharedLogs\Objects;

use Battis\SharedLogs\AbstractObject;

/**
 * @property Device device
 */
class Url extends AbstractObject
{
    const SUPPRESS_DEVICE = false;

    const ID = "url_id";

    public function __construct($databaseRecord, $device = self::SUPPRESS_DEVICE)
    {
        parent::__construct($databaseRecord);

        if ($device instanceof Device) {
            $this->device = $device;
        }
    }

}