<?php
/** LogsBindingTrait */

namespace Battis\SharedLogs\Database\Bindings\Traits;

use Battis\SharedLogs\Database\AbstractBinding;
use Battis\SharedLogs\Database\Bindings\LogsBinding;

/**
 * Provide an as-needed LogsBinding instance
 */
trait LogsBindingTrait
{
    /** @var LogsBinding Binding between Log objects and the `logs` database table*/
    private $logs;

    /**
     * Provide an instance of LogsBinding
     *
     * @uses AbstractBinding::database()
     *
     * @return LogsBinding
     */
    protected function logs()
    {
        if (empty($this->logs)) {
            $this->logs = new LogsBinding($this->database());
        }
        return $this->logs;
    }
}