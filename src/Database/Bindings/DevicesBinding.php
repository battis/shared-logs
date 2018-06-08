<?php
/** DevicesBinding */

namespace Battis\SharedLogs\Database\Bindings;

use Battis\SharedLogs\Database\AbstractBinding;
use Battis\SharedLogs\Database\Bindings\Traits\DevicesBindingTrait;
use Battis\SharedLogs\Database\Bindings\Traits\LogsBindingTrait;
use Battis\SharedLogs\Database\Bindings\Traits\UrlsBindingTrait;
use Battis\SharedLogs\Database\ParameterManager;
use Battis\SharedLogs\Exceptions\BindingException;
use Battis\SharedLogs\Objects\Device;
use Battis\SharedLogs\Objects\Url;
use PDO;

/**
 * A binding between `Device` objects and the database table `devices`
 *
 * @author Seth Battis <seth@battis.net>
 */
class DevicesBinding extends AbstractBinding
{
    use DevicesBindingTrait, UrlsBindingTrait, LogsBindingTrait;

    const INCLUDE_LOGS = 'logs';
    const INCLUDE_URLS = 'urls';

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
    public function all($params = [self::SCOPE_INCLUDE => [self::INCLUDE_LOGS, self::INCLUDE_URLS]])
    {
        return parent::all($params);
    }

    /**
     * Configure ordering of list of bound objects
     *
     * Devices are ordered alphabetically by manufacturer, model, name and then creation date (most recent first).
     *
     * @see AbstractBinding::listOrder()
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
    public function get($id, $params = [self::SCOPE_INCLUDE => [self::INCLUDE_LOGS, self::INCLUDE_URLS]])
    {
        return parent::get($id, $params);
    }

    /**
     * Instantiate bound device when retrieved via `get()`
     *
     * This will process the `'includes'` field of `$params` (an array) and, if it contains the term `'logs'`, the list
     * of logs sub-object will be included in this device object.
     *
     * @see AbstractBinding::SCOPE_INCLUDE
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
        $urls = Device::SUPPRESS_URLS;
        $logs = Device::SUPPRESS_LOGS;
        if (self::parameterValueExists($params, self::SCOPE_INCLUDE, self::INCLUDE_URLS)) {
            $params = self::consumeParameterValue($params, self::SCOPE_INCLUDE, self::INCLUDE_URLS);
            $params = self::consumeParameterValue($params, self::SCOPE_INCLUDE, UrlsBinding::INCLUDE_DEVICE);
            $urls = $this->urls()->listByDevice($databaseRow['id'], $params);
        }
        if (self::parameterValueExists($params, self::SCOPE_INCLUDE, self::INCLUDE_LOGS)) {
            $params = self::consumeParameterValue($params, self::SCOPE_INCLUDE, self::INCLUDE_LOGS);
            $params = self::consumeParameterValue($params, self::SCOPE_INCLUDE, LogsBinding::INCLUDE_DEVICE);
            $logs = $this->logs()->listByDevice($databaseRow['id'], $params);
        }
        return $this->object($databaseRow, $logs, $urls);
    }

    protected function instantiateListedObject($databaseRow, $params)
    {
        return $this->instantiateObject($databaseRow, $params);
    }
}
