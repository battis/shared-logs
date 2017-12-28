<?php

namespace Battis\SharedLogs\Objects;

use Battis\SharedLogs\Object;

class User extends Object
{
    const ID = 'user_id';

    public function jsonSerialize()
    {
        $result = parent::jsonSerialize();
        unset($result['password']);
        return $result;
    }
}