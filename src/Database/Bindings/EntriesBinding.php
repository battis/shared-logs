<?php

namespace Battis\SharedLogs\Database\Bindings;

use Battis\SharedLogs\Database\Binding;
use Battis\SharedLogs\Database\Bindings\Traits\EntriesBindingTrait;
use Battis\SharedLogs\Database\Bindings\Traits\LogsBindingTrait;
use Battis\SharedLogs\Database\Bindings\Traits\UsersBindingTrait;
use Battis\SharedLogs\Objects\Entry;
use Battis\SharedLogs\Objects\Log;
use Battis\SharedLogs\Objects\User;
use PDO;

class EntriesBinding extends Binding
{
    use EntriesBindingTrait, LogsBindingTrait, UsersBindingTrait;

    public function __construct(PDO $database)
    {
        parent::__construct($database, 'entries', Entry::class);
    }

    public function get($id, $params = [self::INCLUDE => ['log', 'user']])
    {
        return parent::get($id, $params);
    }

    protected function instantiateObject($databaseRow, $params)
    {
        $log = Entry::SUPPRESS_LOG;
        $user = Entry::SUPPRESS_USER;
        if (!empty($params[self::INCLUDE]) && is_array($params[self::INCLUDE])) {
            if (in_array('log', $params[self::INCLUDE])) {
                $log = $this->logs()->get($databaseRow[Log::ID], [self::INCLUDE => []]);
            }
            if (in_array('user', $params[self::INCLUDE])) {
                $user = $this->users()->get($databaseRow[User::ID], [self::INCLUDE => []]);
            }
        }
        return $this->object($databaseRow, $log, $user);
    }

    protected function instantiateListedObject($databaseRow, $params)
    {
        return $this->instantiateObject($databaseRow, $params);
    }

    public function listByLog($id, $params = [self::INCLUDE => ['user']])
    {
        $statement = $this->database()->prepare("
            SELECT *
                FROM `" . $this->databaseTable() . "`
                WHERE
                  `" . Log::ID . "` = :id
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