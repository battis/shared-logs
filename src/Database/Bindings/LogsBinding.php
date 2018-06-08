<?php
/** LogsBinding */

namespace Battis\SharedLogs\Database\Bindings;

use Battis\SharedLogs\Database\AbstractBinding;
use Battis\SharedLogs\Database\Bindings\Traits\DevicesBindingTrait;
use Battis\SharedLogs\Database\Bindings\Traits\EntriesBindingTrait;
use Battis\SharedLogs\Database\Bindings\Traits\LogsBindingTrait;
use Battis\SharedLogs\Exceptions\BindingException;
use Battis\SharedLogs\Objects\Device;
use Battis\SharedLogs\Objects\Log;
use PDO;

/**
 * A binding between `Log` objects and the `logs` database table
 *
 * @author Seth Battis <seth@battis.net>
 */
class LogsBinding extends AbstractBinding
{
    use LogsBindingTrait, DevicesBindingTrait, EntriesBindingTrait;

    const INCLUDE_DEVICE = 'device';
    const INCLUDE_ENTRIES = 'entries';
    const INCLUDE_RECENT_ENTRIES = 'recent';

    /**
     * Construct a logs binding from a database connector
     *
     * @param PDO $database
     *
     * @throws BindingException
     */
    public function __construct(PDO $database)
    {
        parent::__construct($database, 'logs', Log::class);
    }

    /**
     * Retrieve all logs from database
     *
     * By default, logs contain a device sub-object
     *
     * @param array $params
     *
     * @return Object[]
     */
    public function all($params = [self::SCOPE_INCLUDE => [self::INCLUDE_DEVICE]])
    {
        return parent::all($params);
    }

    /**
     * Retrieve log by ID from database
     *
     * By default the log contains a device sub-object
     *
     * @param int|string $id
     * @param array $params
     *
     * @return Log|null
     */
    public function get($id, $params = [self::SCOPE_INCLUDE => [self::INCLUDE_DEVICE]])
    {
        return parent::get($id, $params);
    }

    /**
     * Instantiate a log retrieved via `get()`
     *
     * @used-by LogsBinding::instantiateListedObject()
     *
     * @param array $databaseRow
     * @param array $params
     *
     * @return Log
     */
    protected function instantiateObject($databaseRow, $params)
    {
        $device = Log::SUPPRESS_DEVICE;
        $entries = Log::SUPPRESS_ENTRIES;
        if (self::parameterValueExists($params, self::SCOPE_INCLUDE, self::INCLUDE_DEVICE)) {
            $params = self::consumeParameterValue($params, self::SCOPE_INCLUDE, self::INCLUDE_DEVICE);
            $params = self::consumeParameterValue($params, self::SCOPE_INCLUDE, DevicesBinding::INCLUDE_LOGS);
            $device = $this->devices()->get($databaseRow[Device::ID], $params);
        }
        if (self::parameterValueExists($params,self::SCOPE_INCLUDE, self::INCLUDE_ENTRIES)) {
            $params = self::consumeParameterValue($params, self::SCOPE_INCLUDE, self::INCLUDE_ENTRIES);
            $params = self::consumeParameterValue($params, self::SCOPE_INCLUDE, EntriesBinding::INCLUDE_LOG);
            $entries = $this->entries()->listByLog($databaseRow['id'], $params);
        } elseif (self::parameterValueExists($params, self::SCOPE_INCLUDE, self::INCLUDE_RECENT_ENTRIES)) {
            $params = self::consumeParameterValue($params, self::SCOPE_INCLUDE, self::INCLUDE_RECENT_ENTRIES);
            $params = self::consumeParameterValue($params, self::SCOPE_INCLUDE, EntriesBinding::INCLUDE_LOG);
            $params[EntriesBinding::SCOPE_ENTRIES][EntriesBinding::ENTRIES_COUNT] = 1;
            $entries = $this->entries()->listByLog($databaseRow['id'], $params);
        }

        return $this->object($databaseRow, $device, $entries);
    }

    protected function instantiateListedObject($databaseRow, $params)
    {
        return $this->instantiateObject($databaseRow, $params);
    }

    /**
     * Retrieve all logs associated with a specific device, by device ID
     *
     * By default, the logs retrieved will _not_ contain a device sub-object
     *
     * @param string|integer $id Numeric device ID
     * @param array $params (Optional) Associative array of additional request parameters
     *
     * @uses LogsBinding::instantiateListedObject()
     *
     * @return Log[]
     */
    public function listByDevice($id, $params = [])
    {
        $statement = $this->database()->prepare("
            SELECT *
                FROM `" . $this->databaseTable() . "`
                WHERE
                  `" . Device::ID . "` = :id
                ORDER BY
                    " . $this->listOrder() . "
        ");
        $list = [];
        if ($statement->execute(['id' => $id])) {
            while ($row = $statement->fetch()) {
                $list[] = $this->instantiateListedObject($row, $params);
            }
        }
        return $list;
    }
}
