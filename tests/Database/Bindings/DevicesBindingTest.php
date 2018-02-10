<?php

namespace Tests\Database\Bindings;

use Battis\SharedLogs\Database\Bindings\DevicesBinding;
use PHPUnit\DbUnit\DataSet\IDataSet;
use PHPUnit\DbUnit\DataSet\YamlDataSet;
use Tests\Database\DatabaseTestCase;

class DevicesBindingTest extends DatabaseTestCase
{
    /**
     * Returns the test dataset.
     *
     * @return IDataSet
     */
    protected function getDataSet()
    {
        return new YamlDataSet(__DIR__ . '/' . basename(__FILE__) . '/base.yml');
    }
}