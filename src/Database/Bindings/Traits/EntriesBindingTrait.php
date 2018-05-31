<?php
/** EntriesBindingTrait */

namespace Battis\SharedLogs\Database\Bindings\Traits;

use Battis\SharedLogs\Database\AbstractBinding;
use Battis\SharedLogs\Database\Bindings\EntriesBinding;

/**
 * Access to as-needed EntriesBinding instance
 *
 * @author Seth Battis <seth@battis.net>
 */
trait EntriesBindingTrait
{
    /** @var EntriesBinding Binding between Entry objects and `entries` database table */
    private $entries;

    /**
     * Provide an instance of EntriesBinding
     *
     * @uses AbstractBinding::database()
     *
     * @return EntriesBinding
     */
    protected function entries()
    {
        if (empty($this->entries)) {
            $this->entries = new EntriesBinding($this->database());
        }
        return $this->entries;
    }
}