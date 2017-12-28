<?php

namespace Battis\SharedLogs\Database\Bindings\Traits;

use Battis\SharedLogs\Database\Bindings\EntriesBinding;

trait EntriesBindingTrait
{
    /* FIXME There must be a good way of indicating dependence on DatabaseTrait */

    /** @var EntriesBinding */
    private $entries;

    protected function entries()
    {
        if (empty($this->entries)) {
            $this->entries = new EntriesBinding($this->database());
        }
        return $this->entries;
    }
}