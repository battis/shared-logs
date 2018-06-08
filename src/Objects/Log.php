<?php
/** Log */

namespace Battis\SharedLogs\Objects;

use Battis\SharedLogs\Exceptions\ObjectException;
use Battis\SharedLogs\AbstractObject;

/**
 * A log
 *
 * Logs are collections of timestamped entries authored by users about a specific device.
 *
 * @property Device device
 * @property Entry[] entries
 * @author Seth Battis <seth@battis.net>
 */
class Log extends AbstractObject
{
    /** Suppress device sub-object */
    const SUPPRESS_DEVICE = false;

    /** Suppress entries list sub-object */
    const SUPPRESS_ENTRIES = false;

    /** Canonical field name for references to log objects in the database */
    const ID = 'log_id';

    /**
     * Construct a Log object from a database record
     *
     * @param array $databaseRecord Associative array of fields
     * @param Device|false $device (Optional) Device sub-object (or `Log::SUPPRESS::DEVICE`)
     * @param Entry[]|false $entries (Optional) List of entries sub-object (or `Log::SUPPRESS_ENTRIES). Elements that
     *        are not instances of Entry will be ignored.
     *
     * @throws ObjectException If `$databaseRecord` contains no fields.
     */
    public function __construct($databaseRecord, $device = self::SUPPRESS_DEVICE, $entries = self::SUPPRESS_ENTRIES)
    {
        parent::__construct($databaseRecord);

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
