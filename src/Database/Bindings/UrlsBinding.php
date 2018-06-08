<?php
/**
 * Created by PhpStorm.
 * User: sbattis
 * Date: 6/7/2018
 * Time: 2:19 PM
 */

namespace Battis\SharedLogs\Database\Bindings;


use Battis\SharedLogs\Database\AbstractBinding;
use Battis\SharedLogs\Database\Bindings\Traits\DevicesBindingTrait;
use Battis\SharedLogs\Objects\Device;
use Battis\SharedLogs\Objects\Url;
use PDO;

class UrlsBinding extends AbstractBinding
{
    use DevicesBindingTrait;

    const INCLUDE_DEVICE = "device";

    public function __construct(PDO $database)
    {
        parent::__construct($database, "urls", Url::class);
    }

    public function instantiateObject($databaseRow, $params)
    {
        $device = Url::SUPPRESS_DEVICE;
        if (self::parameterExists($params, self::SCOPE_INCLUDE, self::INCLUDE_DEVICE)) {
            $params = self::consumeParameterValue($params, self::SCOPE_INCLUDE, self::INCLUDE_DEVICE);
            $params = self::consumeParameterValue($params, self::SCOPE_INCLUDE, DevicesBinding::INCLUDE_URLS);
            $device = $this->devices()->get($databaseRow[Device::ID], $params);
        }
        return new Url($databaseRow, $device);
    }

    public function instantiateListedObject($databaseRow, $params)
    {
        return $this->instantiateObject($databaseRow, $params);
    }

    /**
     * Retrieve all urls for a specific device, by device ID
     *
     * By default, urls retrieved by this method do _not_ contain a device sub-object.
     *
     * @param integer|string $id Numeric device ID
     * @param array $params (Optional) Associative array of additional request parameters
     * @return Url[]
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

    protected function listOrder()
    {
        return '`name` ASC';
    }
}