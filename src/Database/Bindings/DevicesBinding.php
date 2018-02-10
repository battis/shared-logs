<?php
/** DevicesBinding */

namespace Battis\SharedLogs\Database\Bindings;

use Battis\SharedLogs\Database\Binding;
use Battis\SharedLogs\Database\Bindings\Traits\DevicesBindingTrait;
use Battis\SharedLogs\Database\Bindings\Traits\LogsBindingTrait;
use Battis\SharedLogs\Exceptions\BindingException;
use Battis\SharedLogs\Objects\Device;
use PDO;

/**
 * A binding between `Device` objects and the database table `devices`
 *
 * @author Seth Battis <seth@battis.net>
 */
class DevicesBinding extends Binding
{
    use DevicesBindingTrait, LogsBindingTrait;

    const INCLUDE_LOGS = 'logs';

    /**
     * Construct a device binding from a database connector
     *
     * @param PDO $database
     *
     * @throws BindingException If `$database` is not an instance of PDO
     */
    public function __construct(PDO $database)
    {
        parent::__construct($database, 'devices', Device::class);
    }

    /**
     * Retrieve all devices
     *
     * By default, devices include the list of logs sub-object
     *
     * @param array $params
     *
     * @return Device[]
     */
    public function all($params = [self::INCLUDE => [self::INCLUDE_LOGS]])
    {
        return parent::all($params);
    }

    /**
     * Configure ordering of list of bound objects
     *
     * Devices are ordered alphabetically by manufacturer, model, name and then creation date (most recent first).
     *
     * @see Binding::listOrder()
     *
     * @return string
     */
    protected function listOrder()
    {
        return '`manufacturer` ASC, `model` ASC, `name` ASC, `created` DESC';
    }

    /**
     * Retrieve a specific device by ID
     *
     * Devices default to include a list of logs sub-object
     *
     * @param int|string $id
     * @param array $params
     *
     * @return Device|null
     */
    public function get($id, $params = [self::INCLUDE => [self::INCLUDE_LOGS]])
    {
        return parent::get($id, $params);
    }

    /**
     * Instantiate bound device when retrieved via `get()`
     *
     * This will process the `'includes'` field of `$params` (an array) and, if it contains the term `'logs'`, the list
     * of logs sub-object will be included in this device object.
     *
     * @see Binding::INCLUDE
     *
     * @used-by DevicesBinding::instantiateListedObject()
     *
     * @param array $databaseRow
     * @param array $params
     *
     * @return Device
     */
    protected function instantiateObject($databaseRow, $params)
    {
        $logs = Device::SUPPRESS_LOGS;
        if (!empty($params[self::INCLUDE]) && is_array($params[self::INCLUDE])) {
            if (in_array(self::INCLUDE_LOGS, $params[self::INCLUDE])) {
                $logs = $this->logs()->listByDevice($databaseRow['id']);
            }
        }
        return $this->object($databaseRow, $logs);
    }

    /**
     * Instantiate bound device when retrieved via `all()`
     *
     * This will process the `'includes'` field of `$params` (an array) and, if it contains the term `'logs'`, the list
     * of logs sub-object will be included in this device object.

     * @param array $databaseRow
     * @param array $params
     *
     * @uses DevicesBinding::instantiateObject()
     *
     * @return Device
     */
    protected function instantiateListedObject($databaseRow, $params)
    {
        return $this->instantiateObject($databaseRow, $params);
    }
}
