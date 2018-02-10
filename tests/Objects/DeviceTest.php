<?php

namespace Battis\SharedLogs\Tests\Objects;

use Battis\SharedLogs\AbstractObject;
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
        $this->logs = [new Log(self::$records['logs'][0]), new Log(self::$records['logs'][0])];
    }

    public function testInstantiation()
    {
        $d = new Device($this->device);
        $this->assertInstanceOf(Device::class, $d);
        $this->reconcileFields($this->device, $d);
        return $d;
    }

    /**
     * @depends testInstantiation
     */
    public function testJsonSerialization(AbstractObject $d)
    {
        $json = json_encode($d);
        $this->assertJson($json);
        $this->assertJsonStringEqualsJsonString(json_encode(self::$records['devices'][0]), $json);
    }

    public function testInstantiationWithSingleLog()
    {
        $d = new Device($this->device, $this->singleLog);
        $this->assertInstanceOf(Device::class, $d);
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
        $this->assertJsonStringEqualsJsonString(
            json_encode(array_merge(
                $this->device,
                ['logs' => $this->singleLog]
            )),
            $json
        );
    }

    public function testInstantiationWithMultipleLogs()
    {
        $d = new Device($this->device, $this->logs);
        $this->assertInstanceOf(Device::class, $d);
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
        $this->assertJsonStringEqualsJsonString(
            json_encode(array_merge(
                $this->device,
                ['logs' => $this->logs]
            )),
            $json
        );
    }
}
