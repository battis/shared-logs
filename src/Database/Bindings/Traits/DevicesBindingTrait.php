<?php

namespace Battis\SharedLogs\Database\Bindings\Traits;

use Battis\SharedLogs\Database\Bindings\DevicesBinding;

trait DevicesBindingTrait
{
    /* FIXME There must be a good way of indicating dependence on DatabaseTrait */

    /** @var DevicesBinding */
    private $devices;

    protected function devices()
    {
        if (empty($this->devices)) {
            $this->devices = new DevicesBinding($this->database());
        }
        return $this->devices;
    }
}