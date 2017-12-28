<?php
/** UsersBinding */

namespace Battis\SharedLogs\Database\Bindings;

use Battis\SharedLogs\Database\Binding;
use Battis\SharedLogs\Exceptions\BindingException;
use Battis\SharedLogs\Objects\User;
use PDO;

/**
 * A binding between `User` objects and the `users` database table
 *
 * @author Seth Battis <seth@battis.net>
 */
class UsersBinding extends Binding
{
    /**
     * Construct a users binding from a database connector
     *
     * @param PDO $database
     * @throws BindingException
     */
    public function __construct(PDO $database)
    {
        parent::__construct($database, 'users', User::class);
    }
}