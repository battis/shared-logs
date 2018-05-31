<?php

namespace Battis\SharedLogs\Database;

class Parameters
{
    /** Canonical name for field in additional request parameters containing the list of additional sub-objects */
    const SCOPE_INCLUDE = 'include';

    protected static function consumeParameterValue($params, $scope, $value)
    {
        if (self::parameterValueExists($params, $scope, $value)) {
            $params[$scope] = array_splice($params[$scope], array_search($value, $params[$scope]));
        }
        return $params; // for chaining
    }

    protected static function parameterValueExists($params, $scope, $value)
    {
        return !empty($params[$scope]) && is_array($params[$scope]) && in_array($value, $params[$scope]);
    }

    protected static function scopeExists($params, $scope)
    {
        return !empty($params[$scope]);
    }

    protected static function getScope($params, $scope)
    {
        if (self::scopeExists($params, $scope)) {
            return $params[$scope];
        }
        return null;
    }
}