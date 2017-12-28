<?php

namespace Battis\SharedLogs\Database\Bindings\Traits;

use Battis\SharedLogs\Database\Bindings\LogsBinding;

trait LogsBindingTrait
{
    /* FIXME There must be a good way of indicating dependence on DatabaseTrait */

    /** @var LogsBinding */
    private $logs;

    protected function logs()
    {
        if (empty($this->logs)) {
            $this->logs = new LogsBinding($this->database());
        }
        return $this->logs;
    }
}