<?php

namespace Tests\Objects;

use Battis\SharedLogs\Objects\Device;
use Battis\SharedLogs\Objects\Log;
use PHPUnit\Framework\TestCase;

class DeviceTest extends TestCase
{
    private static $SIMPLE_RECORD = [
        'id' => '1',
        'manufacturer' => 'Foo',
        'model' => 'Bar',
        'name' => 'Baz',
        'created' => '2018-02-06 12:34:56',
        'modified' => '2018-02-07 13:57:00'
    ];

    private static $LOGS = [[
       'id' => '2',
       'name' => 'Baz Maintenance',
        'device_id' => '1',
        'created' => '2018-02-08 14:25:36',
        'modified' => '2018-02-09 15:26:37'
    ]];

    public function testInstantiation()
    {
        $d = new Device(self::$SIMPLE_RECORD);
        $this->assertInstanceOf(Device::class, $d);
        $this->assertEquals(self::$SIMPLE_RECORD['id'], $d->id);
        $this->assertEquals(self::$SIMPLE_RECORD['manufacturer'], $d->manufacturer);
        $this->assertEquals(self::$SIMPLE_RECORD['model'], $d->model);
        $this->assertEquals(self::$SIMPLE_RECORD['name'], $d->name);
        $this->assertEquals(self::$SIMPLE_RECORD['created'], $d->created);
        $this->assertEquals(self::$SIMPLE_RECORD['modified'], $d->modified);
        return $d;
    }

    public function testInstantiationWithLogs()
    {
        $logs = [];
        foreach (self::$LOGS as $log) {
            $logs[] = new Log($log);
        }
        $d = new Device(self::$SIMPLE_RECORD, $logs);
        $this->assertInstanceOf(Device::class, $d);
        $this->assertEquals(self::$SIMPLE_RECORD['id'], $d->id);
        $this->assertEquals(self::$SIMPLE_RECORD['manufacturer'], $d->manufacturer);
        $this->assertEquals(self::$SIMPLE_RECORD['model'], $d->model);
        $this->assertEquals(self::$SIMPLE_RECORD['name'], $d->name);
        $this->assertEquals(self::$SIMPLE_RECORD['created'], $d->created);
        $this->assertEquals(self::$SIMPLE_RECORD['modified'], $d->modified);
        $this->assertTrue(is_array($d->logs));
        foreach ($d->logs as $log) {
            $this->assertInstanceOf(Log::class, $log);
        }
        return $d;
    }

    /**
     * @depends testInstantiation
     */
    public function testJsonSerialization(Device $d)
    {
        $json = json_encode($d);
        $this->assertJson($json);
        $this->assertJsonStringEqualsJsonString(json_encode(self::$SIMPLE_RECORD), $json);
    }

    /**
     * @depends testInstantiationWithLogs
     */
    public function testJsonSerializationWithLogs(Device $d)
    {
        $json = json_encode($d);
        $this->assertJson($json);
        $this->assertJsonStringEqualsJsonString(json_encode(array_merge(self::$SIMPLE_RECORD, ['logs' => self::$LOGS])), $json);
    }
}