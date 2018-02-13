<?php

namespace Battis\SharedLogs\Tests\Objects;

use Battis\SharedLogs\AbstractObject;
use Battis\SharedLogs\Exceptions\ObjectException;
use Battis\SharedLogs\Objects\Entry;
use Battis\SharedLogs\Objects\Log;
use Battis\SharedLogs\Tests\AbstractObjectTest;

class LogTest extends AbstractObjectTest
{
    protected $log;
    protected $device;
    protected $singleEntry;
    protected $entries;

    protected function setUp()
    {
        parent::setUp();
        $this->log = self::$records['logs'][0];
        $this->device = self::$records['devices'][0];
        $this->singleEntry = [new Entry(self::$records['entries'][0])];
        $this->entries = [new Entry(self::$records['entries'][0]), new Entry(self::$records['entries'][1])];
    }

    public function testInvalidInstantiation()
    {
        $this->expectException(ObjectException::class);
        $this->expectExceptionCode(ObjectException::MISSING_DATABASE_RECORD);
        $e = new Log("foo bar");
    }

    public function testInstantiation()
    {
        $l = new Log($this->log);
        $this->reconcileFields($this->log, $l);
        $this->assertObjectNotHasAttribute('entries', $l);

        return $l;
    }

    /**
     * @depends testInstantiation
     */
    public function testJsonSerialization(AbstractObject $l)
    {
        $json = json_encode($l);
        $this->assertJson($json);
        $this->assertJsonStringEqualsJsonFile(self::$jsonDir . '/log.json', $json);
    }
}
