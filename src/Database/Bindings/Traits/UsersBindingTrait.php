<?php
/** UsersBindingTrait */

namespace Battis\SharedLogs\Database\Bindings\Traits;

use Battis\SharedLogs\Database\AbstractBinding;
use Battis\SharedLogs\Database\Bindings\UsersBinding;

/**
 * Provide an as-needed instance of UsersBinding
 *
 * @author Seth Battis <sseth@battis.net>
 */
trait UsersBindingTrait
{
    /** @var UsersBinding Binding between User objects and `users` database table */
    private $users;

    /**
     * Provide an instance of UsersBinding
     *
     * @uses AbstractBinding::$database()
     *
     * @return UsersBinding
     */
    protected function users()
    {
        if (empty($this->users)) {
            $this->users = new UsersBinding($this->database());
        }
        return $this->users;
    }
}