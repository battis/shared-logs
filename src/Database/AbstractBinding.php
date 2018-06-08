<?php
/** Binding */

namespace Battis\SharedLogs\Database;

use Battis\SharedLogs\Exceptions\BindingException;
use Battis\SharedLogs\AbstractObject;
use PDO;

/**
 * A binding between and Object and a database table
 *
 * @author Seth Battis <seth@battis.net>
 * @see Object
 *
 * TODO Convert database errors into clearer API responses
 * TODO Add search capabilities to all()
 */
abstract class AbstractBinding extends ParameterManager
{
    /** @var PDO Database connector */
    private $database;

    /** @var string Name of database table bound to this object*/
    private $databaseTable;

    /** @var string The class of all objects created by this binding */
    private $object = AbstractObject::class;

    /**
     * Binding constructor
     *
     * @param PDO $database Database connector
     * @param string $databaseTable Name of the bound database table
     * @param string $type Fully-qualified class name of bound object (e.g. `AbstractObject::class` or
     *        `"\Battis\SharedLogs\Object"`)
     *
     * @throws BindingException If `$databases` is not an instance of PDO
     */
    public function __construct(PDO $database, $databaseTable, $type)
    {
        $this->initDatabase($database);
        $this->initDatabaseTable($databaseTable);
        $this->initObject($type);
    }

    /**
     * Initialize the database connector
     *
     * @param PDO $database Database connector
     *
     * @throws BindingException If `$database` is not an instance of PDO
     */
    private function initDatabase(PDO $database)
    {
        if ($database instanceof PDO) {
            $this->database = $database;
        } else {
            throw new BindingException(
                'Cannot create binding without datasbase connector',
                BindingException::MISSING_DATABASE_CONNECTOR
            );
        }
    }

    /**
     * The database connector
     *
     * @return PDO
     */
    protected function database()
    {
        return $this->database;
    }

    /**
     * Initialize the name of the bound database table
     *
     * @param string $table Name of bound table in database
     */
    private function initDatabaseTable($table)
    {
        $table = (string) $table;
        if (!empty($table)) {
            $this->databaseTable = $table;
        }
    }

    /**
     * The name of the bound database table
     *
     * @return string
     */
    protected function databaseTable()
    {
        return $this->databaseTable;
    }

    /**
     * Initialize the bound object class
     *
     * @param string $type Fully-qualified class name of bound object (e.g. `Object::class` or
     *        `"\Battis\SharedLogs\Object"`)
     */
    private function initObject($type)
    {
        $type = (string) $type;
        if (!empty($type)) {
            $this->object = $type;
        }
    }

    /**
     * Instantiate the bound object
     *
     * @param array ...$params
     * @return AbstractObject
     */
    protected function object(...$params)
    {
        return new $this->object(...$params);
    }

    /**
     * Retrieve a single object from the bound database table
     *
     * @param integer|string $id A valid numeric object ID
     * @param array $params (Optional) Associative array of additional request parameters
     *
     * @uses AbstractBinding::instantiateObject()
     *
     * @return AbstractObject|null `NULL` if no object is found in database
     */
    public function get($id, $params = [])
    {
        if (is_numeric($id)) {
            $statement = $this->database()->prepare("
            SELECT *
                FROM `" . $this->databaseTable . "`
                WHERE
                  `id` = :id
        ");
            if ($statement->execute(['id' => $id])) {
                return $this->instantiateObject($statement->fetch(), $params);
            }
        }
        return null;
    }

    /**
     * Instantiate bound object when retrieved via `get()`
     *
     * Presumably similar to the instantiation when retrieved via `all()`, but there may be some desire to, for example,
     * limit the depth of nested subobjects in the context of a list.
     *
     * @used-by AbstractBinding::get()
     *
     * @param array $databaseRow Bound row of database table
     * @param array $params Associative array of additional request parameters
     *
     * @return AbstractObject
     *
     * @see AbstractBinding::instantiateListedObject()
     */
    protected function instantiateObject($databaseRow, $params)
    {
        return $this->object($databaseRow);
    }

    /**
     * Retrieve all objects from bound database table
     *
     * @param array $params (Optional) Associative array of additional request parameters
     *
     * @uses AbstractBinding::listOrder()
     * @uses AbstractBinding::instantiateListedObject()
     *
     * @return AbstractObject[]
     */
    public function all($params = [])
    {
        $statement = $this->database()->query("
            SELECT *
                FROM `" . $this->databaseTable() . "`
            ORDER BY
                " . $this->listOrder() . "
        ");
        $list = [];
        while ($row = $statement->fetch()) {
            $list[] = $this->instantiateListedObject($row, $params);
        }
        return $list;
    }

    /**
     * Configure ordering of list of bound objects
     *
     * The default ordering is to sort the list by descending creation date (most recent first)
     *
     * @used-by AbstractBinding::all()
     *
     * @return string
     */
    protected function listOrder()
    {
        return '`created` DESC';
    }

    /**
     * Instantiate bound object when retrieved via `all()`
     *
     * Presumably similar to the instantiation when retrieved via `get()`, but there may be some desire to, for example,
     * limit the depth of nested subobjects in the context of a list.
     *
     * @used-by AbstractBinding::all()
     *
     * @param array $databaseRow Associative array of fields from database to initialize object
     * @param array $params Associative array of additional request parameters
     *
     * @return AbstractObject
     *
     * @see AbstractBinding::instantiateObject()
     */
    protected function instantiateListedObject($databaseRow, $params)
    {
        return $this->object($databaseRow);
    }

    /**
     * Create a new instance of the bound object and store in database
     *
     * @param array $params Associative array of fields to initialize in created Object (will potentially be passed on
     *        as additional request parameters to `get()`)
     *
     * @uses AbstractBinding::getCreatedObject()
     *
     * @return AbstractObject|null `NULL` if the object cannot be created in the database
     */
    public function create($params)
    {
        $statement = $this->database()->prepare("
            INSERT
              INTO `" . $this->databaseTable() . "`
              (`" . implode('`, `', array_keys($params)) . "`) VALUES
              (:" . implode(', :', array_keys($params)) . ")
        ");
        if ($statement->execute($params)) {
            return $this->getCreatedObject($this->database()->lastInsertId(), $params);
        }
        return null;
    }

    /**
     * Retrieve a recently created object from database
     *
     * The default implementation of this method simply passes the ID on to `get()` to retrieve and return the recently
     * created object. Presumably similar to `getUpdatedObject()` or `getDeletedObject()`.
     *
     * @used-by AbstractBinding::create()
     *
     * @param integer|string $id Numeric ID of recently created object in database
     * @param array $params Associative array of additional request parameters
     *
     * @return AbstractObject|null `NULL` if object cannot be retrieved from database
     *
     * @see AbstractBinding::getUpdatedObject()
     * @see AbstractBinding::getDeletedObject()
     */
    protected function getCreatedObject($id, $params)
    {
        return $this->get($id, $params);
    }

    /**
     * Update an existing object in the database
     *
     * TODO Disambiguate reasons for errors in updating -- separate "can't be found" from "found, but can't be changed"
     *
     * @param integer|string $id ID of the object to be updated
     * @param array $params Associative array of fields to be updated (will also be potentially passed to `get()` as
     *        additional request parameters
     *
     * @uses AbstractBinding::getUpdatedObject()
     *
     * @return AbstractObject|null `NULL` if the specified object cannot be found or cannot be updated as requested
     */
    public function update($id, $params)
    {
        $assignments = [];
        unset($params['id']);
        foreach (array_keys($params) as $key) {
            $assignments[] = "`$key` = ':$key'";
        }
        $statement = $this->database()->prepare("
            UPDATE `" . $this->databaseTable() . "`
                SET
                  " . implode(', ', $assignments) . "
                WHERE
                    `id` = :id
        ");
        if ($statement->execute(array_merge(['id' => $id], $params))) {
            return $this->getUpdatedObject($id, $params);
        }
        return null;
    }

    /**
     * Retrieve a recently updated object from database
     *
     * The default implementation of this method simply passes the ID on to `get()` to retrieve and return the recently
     * updated object. Presumably similar to `getCreatedObject()` and `getDeletedObject()`.
     *
     * @param integer|string $id
     * @param array $params
     * @return AbstractObject|null
     *
     * @see AbstractBinding::getCreatedObject()
     * @see AbstractBinding::getDeletedObject()
     */
    protected function getUpdatedObject($id, $params)
    {
        return $this->get($id, $params);
    }

    /**
     * Delete an object from the bound database
     *
     * TODO Disambiguate null responses of "can't find it" and "found it, but can't delete it"
     *
     * @param integer|string $id Numeric ID of the object to be deleted
     * @param array $params (Optional) Associative array of additional request parameters
     *
     * @uses AbstractBinding::getDeletedObject()
     *
     * @return AbstractObject|null `NULL` if the object is not found or cannot be deleted
     */
    public function delete($id, $params = [])
    {
        $object = $this->getDeletedObject($id, $params);
        if ($object instanceof AbstractObject) {
            $statement = $this->database()->prepare("
                DELETE FROM `" . $this->databaseTable() . "`
                    WHERE
                        `id` = :id
            ");
            if ($statement->execute(['id' => $id])) {
                return $object;
            }
        }
        return null;
    }

    /**
     * Retrieve a soon-to-be deleted object from database
     *
     * The default implementation of this method simply passes the ID on to `get()` to retrieve and return the
     * soon-to-be deleted object. Presumably similar to `getCreatedObject()` and `getUpdatedObject()`.
     *
     * @used-by AbstractBinding::delete()
     *
     * @param integer}string $id Numeric ID of the object to be retrieved
     * @param array $params Associative array of additional request parameters
     *
     * @return AbstractObject|null `NULL` if the object specified cannot be retrieved
     *
     * @see AbstractBinding::getCreatedObject()
     * @see AbstractBinding::getUpdatedObject()
     */
    protected function getDeletedObject($id, $params)
    {
        return $this->get($id, $params);
    }
}
