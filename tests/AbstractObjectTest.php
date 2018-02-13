<?php

namespace Battis\SharedLogs\Tests;

use Battis\SharedLogs\AbstractObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractObjectTest extends TestCase
{
    protected static $records;
    protected static $jsonDir = __DIR__ . '/data/json';

    protected function setUp()
    {
        if (empty($this->records)) {
            self::$records = Yaml::parseFile(__DIR__ . '/data/yml/base.yml');
        }
    }

    protected function reconcileFields($record, $object)
    {
        foreach ($record as $key => $value) {
            $this->assertEquals($value, $object->$key);
        }
    }

    /**
     * @return AbstractObject
     */
    abstract public function testInstantiation();

    abstract public function testInvalidInstantiation();

    /**
     * @depends testInstantiation
     */
    abstract public function testJsonSerialization(AbstractObject $o);
}
