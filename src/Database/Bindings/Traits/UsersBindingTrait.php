<?php

namespace Battis\SharedLogs\Database\Bindings\Traits;

use Battis\SharedLogs\Database\Bindings\UsersBinding;

trait UsersBindingTrait
{
    /* FIXME There must be a good way of indicating dependence on DatabaseTrait */

    /** @var UsersBinding */
    private $users;

    protected function users()
    {
        if (empty($this->users)) {
            $this->users = new UsersBinding($this->database());
        }
        return $this->users;
    }
}