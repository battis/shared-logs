<?php

namespace Battis\SharedLogs;

use Battis\SharedLogs\Exceptions\ObjectException;
use JsonSerializable;

/**
 * A generic object bound to a database table
 *
 * @author Seth Battis <seth@battis.net>
 */
abstract class Object implements JsonSerializable
{
    /**
     * Construct an object from the corresponding row of a bound database table
     *
     * @param array $databaseRow Associative array
     *
     * @uses Object::validateParams()
     *
     * @throws ObjectException If no parameters are provided
     */
    public function __construct($databaseRow)
    {
        foreach($this->validateParams($databaseRow) as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Validate (and filter) a row of database information before applying it to the object
     *
     * @param array $databaseRow Associative array
     * @return array Filtered and validated
     * @throws ObjectException If no parameters are provided
     */
    protected function validateParams($databaseRow) {
        /* TODO Convert MySQL timestamp to SOAP timestamp for portability */
        if (is_array($databaseRow)) {
            return $databaseRow;
        } else {
            throw new ObjectException('No parameters provided to from which construct object', ObjectException::MISSING_ARGUMENTS);
        }
    }

    /**
     * Prepare the object for JSON serialization
     *
     * @see JsonSerializable::jsonSerialize()
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(['type' => static::class], get_object_vars($this));
    }
}