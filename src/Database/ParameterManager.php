<?php

namespace Battis\SharedLogs\Database;

class ParameterManager
{
    /** Canonical name for field in additional request parameters containing the list of additional sub-objects */
    const SCOPE_INCLUDE = 'include';

    protected static function parameterExists($params, $scope, $key)
    {
        return !empty($params[$scope][$key]);
    }

    protected static function parameterValueExists($params, $scope, $value)
    {
        return !empty($params[$scope]) && is_array($params[$scope]) && in_array($value, $params[$scope]);
    }

    protected static function getParameterValue($params, $scope, $key, $defaultValue = null) {
        if (self::parameterExists($params, $scope, $key)) {
            return $params[$scope][$key];
        }
        return $defaultValue;
    }

    protected static function consumeParameterValue($params, $scope, $value)
    {
        if (self::parameterValueExists($params, $scope, $value)) {
            unset($params[$scope][array_search($value, $params[$scope])]);
        }
        return $params; // for chaining
    }

    protected static function scopeExists($params, $scope)
    {
        return !empty($params[$scope]);
    }

    protected static function getScope($params, $scope, $defaultValue = null)
    {
        if (self::scopeExists($params, $scope)) {
            return $params[$scope];
        }
        return $defaultValue;
    }
}