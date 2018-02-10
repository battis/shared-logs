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

    public function lookupByScreenName($screen_name, $params = [])
    {
        $_screen_name = (string) $screen_name;
        if (strlen($_screen_name) >= User::SCREEN_NAME_MINIMUM_LENGTH) {
            $statement = $this->database()->prepare("
            SELECT *
                FROM `" . $this->databaseTable() . "`
                WHERE
                  `screen_name` = :screen_name
        ");
            if ($statement->execute(['screen_name' => $_screen_name])) {
                return $this->instantiateObject($statement->fetch(), $params);
            }
        }
        return null;
    }
}
