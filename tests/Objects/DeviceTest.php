<?php

namespace Battis\SharedLogs\Tests\Objects;

use Battis\SharedLogs\AbstractObject;
use Battis\SharedLogs\Exceptions\ObjectException;
use Battis\SharedLogs\Objects\Device;
use Battis\SharedLogs\Objects\Log;
use Battis\SharedLogs\Tests\AbstractObjectTest;
use PHPUnit\Framework\TestCase;

class DeviceTest extends AbstractObjectTest
{
    protected $device;
    protected $singleLog;
    protected $logs;

    protected function setUp()
    {
        parent::setUp();
        $this->device = self::$records['devices'][0];
        $this->singleLog = [new Log(self::$records['logs'][0])];
        $this->logs = [new Log(self::$records['logs'][0]), new Log(self::$records['logs'][1])];
    }

    public function testInvalidInstantiation()
    {
        $this->expectException(ObjectException::class);
        $this->expectExceptionCode(ObjectException::MISSING_DATABASE_RECORD);
        $d = new Device("foo bar");
    }

    public function testInstantiation()
    {
        $d = new Device($this->device);
        $this->reconcileFields($this->device, $d);
        $this->assertObjectNotHasAttribute('logs', $d);
        return $d;
    }

    /**
     * @depends testInstantiation
     */
    public function testJsonSerialization(AbstractObject $d)
    {
        $json = json_encode($d);
        $this->assertJson($json);
        $this->assertJsonStringEqualsJsonFile(self::$jsonDir . '/device.json', $json);
    }

    public function testInstantiationWithSingleLog()
    {
        $d = new Device($this->device, $this->singleLog);
        $this->reconcileFields($this->device, $d);
        $this->assertEquals(1, count($d->logs));
        $this->reconcileFields($this->singleLog[0], $d->logs[0]);

        return $d;
    }

    /**
     * @depends testInstantiationWithSingleLog
     */
    public function testJsonSerializationWithSingleLog(AbstractObject $d)
    {
        $json = json_encode($d);
        $this->assertJson($json);
        $this->assertJsonStringEqualsJsonFile(self::$jsonDir . '/deviceWithSingleLog.json', $json);
    }

    public function testInstantiationWithMultipleLogs()
    {
        $d = new Device($this->device, $this->logs);
        $this->reconcileFields($this->device, $d);
        for ($i = 0; $i < count($this->logs); $i++) {
            $this->reconcileFields($this->logs[$i], $d->logs[$i]);
        }

        return $d;
    }

    /**
     * @depends testInstantiationWithMultipleLogs
     */
    public function testJsonSerializationWithMultipleLogs(AbstractObject $d)
    {
        $json = json_encode($d);
        $this->assertJson($json);
        $this->assertJsonStringEqualsJsonFile(self::$jsonDir . '/deviceWithLogs.json', $json);
    }

    public function testInstantiationWithMismatchedNestedRecords()
    {
        $d = new Device($this->device, "foo bar");
        $this->assertObjectNotHasAttribute('logs', $d);

        $d = new Device($this->device, $this->singleLog[0]);
        $this->assertObjectNotHasAttribute('logs', $d);
    }
}
