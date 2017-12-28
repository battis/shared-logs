<?php

namespace Battis\SharedLogs\Database\Bindings;

use Battis\SharedLogs\Database\Binding;
use Battis\SharedLogs\Objects\User;
use PDO;

class UsersBinding extends Binding
{
    public function __construct(PDO $database)
    {
        parent::__construct($database, 'users', User::class);
    }
}