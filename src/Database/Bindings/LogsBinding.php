<?php

namespace Battis\SharedLogs\Database\Bindings;

use Battis\SharedLogs\Database\Binding;
use Battis\SharedLogs\Database\Bindings\Traits\DevicesBindingTrait;
use Battis\SharedLogs\Database\Bindings\Traits\EntriesBindingTrait;
use Battis\SharedLogs\Database\Bindings\Traits\LogsBindingTrait;
use Battis\SharedLogs\Objects\Device;
use Battis\SharedLogs\Objects\Log;
use PDO;

class LogsBinding extends Binding
{    use LogsBindingTrait, DevicesBindingTrait, EntriesBindingTrait;


    public function __construct(PDO $database)
    {
        parent::__construct($database, 'logs', Log::class);
    }

    public function all($params = [self::INCLUDE => 'device'])
    {
        return parent::all($params);
    }

    public function get($id, $params = [self::INCLUDE => 'device'])
    {
        return parent::get($id, $params);
    }

    protected function instantiateObject($databaseRow, $params)
    {
        $device = Log::SUPPRESS_DEVICE;
        $entries = Log::SUPPRESS_ENTRIES;
        if (!empty($params[self::INCLUDE]) && is_array($params[self::INCLUDE])) {
            if (in_array('device', $params[self::INCLUDE])) {
                $device = $this->devices()->get($databaseRow[Device::ID], [self::INCLUDE => []]);
            }
            if (in_array('entries', $params[self::INCLUDE])) {
                $entries = $this->entries()->listByLog($databaseRow['id']);
            }
        }
        return $this->object($databaseRow, $device, $entries);
    }

    protected function instantiateListedObject($databaseRow, $params)
    {
        return $this->instantiateObject($databaseRow, $params);
    }

    public function listByDevice($id, $params = [self::INCLUDE => []])
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