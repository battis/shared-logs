<?php

namespace Battis\SharedLogs\Database\Bindings;

use Battis\SharedLogs\Database\Binding;
use Battis\SharedLogs\Database\Bindings\Traits\DevicesBindingTrait;
use Battis\SharedLogs\Database\Bindings\Traits\LogsBindingTrait;
use Battis\SharedLogs\Objects\Device;
use PDO;

class DevicesBinding extends Binding
{
    use DevicesBindingTrait, LogsBindingTrait;

    public function __construct(PDO $database)
    {
        parent::__construct($database, 'devices', Device::class);
    }

    public function all($params = [self::INCLUDE => ['logs']])
    {
        return parent::all($params);
    }

    protected function listOrder()
    {
        return '`manufacturer` ASC, `model` ASC, `name` ASC, `created` DESC';
    }

    public function get($id, $params = [self::INCLUDE => ['logs']])
    {
        return parent::get($id, $params);
    }

    protected function instantiateObject($databaseRow, $params)
    {
        $logs = [];
        if (!empty($params[self::INCLUDE]) && is_array($params[self::INCLUDE])) {
            if (in_array('logs', $params[self::INCLUDE])) {
                $logs = $this->logs()->listByDevice($databaseRow['id']);
            }
        }
        return $this->object($databaseRow, $logs);
    }

    protected function instantiateListedObject($databaseRow, $params)
    {
        return $this->instantiateObject($databaseRow, $params);
    }
}