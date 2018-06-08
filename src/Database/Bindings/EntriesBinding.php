<?php
/** EntriesBinding */

namespace Battis\SharedLogs\Database\Bindings;

use Battis\SharedLogs\Database\AbstractBinding;
use Battis\SharedLogs\Database\Bindings\Traits\EntriesBindingTrait;
use Battis\SharedLogs\Database\Bindings\Traits\LogsBindingTrait;
use Battis\SharedLogs\Database\Bindings\Traits\UsersBindingTrait;
use Battis\SharedLogs\Exceptions\BindingException;
use Battis\SharedLogs\Objects\Entry;
use Battis\SharedLogs\Objects\Log;
use Battis\SharedLogs\Objects\User;
use PDO;

/**
 * A binding between `Entry` objects and the `entries` database table
 *
 * @author Seth Battis <seth@battis.net>
 */
class EntriesBinding extends AbstractBinding
{
    use EntriesBindingTrait, LogsBindingTrait, UsersBindingTrait;

    const SCOPE_ENTRIES = 'entries';
    const ENTRIES_COUNT = 'count';

    const INCLUDE_USER = 'user';
    const INCLUDE_LOG = 'log';

    /**
     * Constuct an entries binding from a database connector
     *
     * @param PDO $database
     *
     * @throws BindingException
     */
    public function __construct(PDO $database)
    {
        parent::__construct($database, 'entries', Entry::class);
    }

    /**
     * Retrieve an entry by ID
     *
     * By default, entries contain both log and user sub-objects.
     *
     * @param int|string $id
     * @param array $params
     *
     * @return Entry|null
     */
    public function get($id, $params = [self::SCOPE_INCLUDE => [self::INCLUDE_LOG, self::INCLUDE_USER]])
    {
        return parent::get($id, $params);
    }

    /**
     * Instantiate an entry retrieved via `get()`
     *
     * @used-by EntriesBinding::instantiateListedObject()
     *
     * @param array $databaseRow
     * @param array $params
     *
     * @return Entry
     */
    protected function instantiateObject($databaseRow, $params)
    {
        $log = Entry::SUPPRESS_LOG;
        $user = Entry::SUPPRESS_USER;
        if (self::parameterValueExists($params,self::SCOPE_INCLUDE,self::INCLUDE_LOG)) {
            $params = self::consumeParameterValue($params,self::SCOPE_INCLUDE,self::INCLUDE_LOG);
            $params = self::consumeParameterValue($params,self::SCOPE_INCLUDE,LogsBinding::INCLUDE_ENTRIES);
            $log = $this->logs()->get($databaseRow[Log::ID], $params);
        }
        if (self::parameterValueExists($params, self::SCOPE_INCLUDE, self::INCLUDE_USER)) {
            $params = self::consumeParameterValue($params, self::SCOPE_INCLUDE,self::INCLUDE_USER);
            $user = $this->users()->get($databaseRow[User::ID], $params);
        }
        return $this->object($databaseRow, $log, $user);
    }

    protected function instantiateListedObject($databaseRow, $params)
    {
        return $this->instantiateObject($databaseRow, $params);
    }

    /**
     * Retrieve all entries in a specific log, by log ID
     *
     * By default, entries retrieved by this method contain user sub-object, but _not_ a log sub-object.
     *
     * @param integer|string $id Numeric log ID
     * @param array $params (Optional) Associative array of additional request parameters
     * @return Entry[]
     */
    public function listByLog($id, $params = [self::SCOPE_INCLUDE => [self::INCLUDE_USER]])
    {
        $count = self::getParameterValue($params, self::SCOPE_ENTRIES, self::ENTRIES_COUNT, false);
        $statement = $this->database()->prepare("
            SELECT *
                FROM `" . $this->databaseTable() . "`
                WHERE
                  `" . Log::ID . "` = :id
                ORDER BY
                    " . $this->listOrder() . "
                " . ($count ? "LIMIT $count" : "") . "
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
