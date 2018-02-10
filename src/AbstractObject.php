<?php
/** Object */

namespace Battis\SharedLogs;

use Battis\SharedLogs\Exceptions\ObjectException;
use JsonSerializable;

/**
 * A generic object bound to a database table

 * Implements `JsonSerializable` for easy output as a JSON response via the API.
 *
 * @author Seth Battis <seth@battis.net>
 */
abstract class AbstractObject implements JsonSerializable
{
    /**
     * Construct an object from the corresponding row of a bound database table
     *
     * @param array $databaseRecord Associative array of fields
     *
     * @uses Object::validateParams()
     *
     * @throws ObjectException If an empty database record is provided
     */
    public function __construct($databaseRecord)
    {
        foreach ($this->validateParams($databaseRecord) as $key => $value) {
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
    protected function validateParams($databaseRow)
    {
        /* TODO Convert MySQL timestamp to SOAP timestamp for portability */
        if (is_array($databaseRow)) {
            return $databaseRow;
        } else {
            throw new ObjectException(
                'No parameters provided to from which construct object',
                ObjectException::MISSING_DATABASE_RECORD
            );
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
        return get_object_vars($this);
    }
}
