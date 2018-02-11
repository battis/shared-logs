<?php

namespace Battis\SharedLogs\Tests\Objects;

use Battis\SharedLogs\AbstractObject;
use Battis\SharedLogs\Exceptions\ObjectException;
use Battis\SharedLogs\Objects\Entry;
use Battis\SharedLogs\Objects\Log;
use Battis\SharedLogs\Objects\User;
use Battis\SharedLogs\Tests\AbstractObjectTest;

class EntryTest extends AbstractObjectTest
{
    protected $entry;
    protected $log;
    protected $wrongLog;
    protected $user;
    protected $wrongUser;

    protected function setUp()
    {
        parent::setUp();
        $this->entry = self::$records['entries'][0];
        $this->log = new Log(self::$records['logs'][0]);
        $this->wrongLog = new Log(self::$records['logs'][1]);
        $this->user = new User(self::$records['users'][0]);
        $this->wrongUser = new User(self::$records['users'][1]);
    }

    public function testInvalidInstantiation()
    {
        $this->expectException(ObjectException::class);
        $this->expectExceptionCode(ObjectException::MISSING_DATABASE_RECORD);
        $e = new Entry("foo bar");
    }

    public function testInstantiation()
    {
        $e = new Entry($this->entry);
        $this->reconcileFields($this->entry, $e);
        $this->assertObjectNotHasAttribute('log', $e);
        $this->assertObjectNotHasAttribute('user', $e);

        return $e;
    }

    /**
     * @depends testInstantiation
     */
    public function testJsonSerialization(AbstractObject $e)
    {
        $json = json_encode($e);
        $this->assertJson($json);
        $this->assertJsonStringEqualsJsonString(json_encode($this->entry), $json);
    }

    public function testInstantiationWithLog()
    {
        $e = new Entry($this->entry, $this->log);
        $this->reconcileFields($this->entry, $e);
        $this->reconcileFields($this->log, $e->log);
        $this->assertObjectNotHasAttribute('user', $e);

        return $e;
    }

    /**
     * @depends testInstantiationWithLog
     */
    public function testJsonSerializationWithLog(AbstractObject $e)
    {
        $json = json_encode($e);
        $this->assertJson($json);
        $this->assertJsonStringEqualsJsonString(json_encode(array_merge($this->entry, ['log' => $this->log])), $json);
    }

    public function testInstantiationWithUser()
    {
        $e = new Entry($this->entry, Entry::SUPPRESS_LOG, $this->user);
        $this->reconcileFields($this->entry, $e);
        $this->assertObjectNotHasAttribute('log', $e);
        $this->reconcileFields($this->user, $e->user);

        return $e;
    }

    /**
     * @depends testInstantiationWithUser
     */
    public function testJsonSerializationWithUser(AbstractObject $e)
    {
        $json = json_encode($e);
        $this->assertJson($json);
        $this->assertJsonStringEqualsJsonString(json_encode(array_merge($this->entry, ['user' => $this->user])), $json);
    }

    public function testInstantiationWithLogAndUser()
    {
        $e = new Entry($this->entry, $this->log, $this->user);
        $this->reconcileFields($this->entry, $e);
        $this->reconcileFields($this->log, $e->log);
        $this->reconcileFields($this->user, $e->user);

        return $e;
    }

    /**
     * @depends testInstantiationWithLogAndUser
     */
    public function testJsonSerializationWithLogAndUser(AbstractObject $e)
    {
        $json = json_encode($e);
        $this->assertJson($json);
        $this->assertJsonStringEqualsJsonString(json_encode(array_merge(
            $this->entry,
            [
                'log' => $this->log,
                'user' => $this->user
            ]
        )), $json);
    }

    public function testInstantiationWithMismatchedNestedRecords()
    {
        $this->expectException(ObjectException::class);
        $this->expectExceptionCode(ObjectException::NESTED_RECORD_MISMATCH);
        $e = new Entry($this->entry, $this->wrongLog);

        $this->expectException(ObjectException::class);
        $this->expectExceptionCode(ObjectException::NESTED_RECORD_MISMATCH);
        $e = new Entry($this->entry, Entry::SUPPRESS_LOG, $this->wrongUser);

        $this->expectException(ObjectException::class);
        $this->expectExceptionCode(ObjectException::NESTED_RECORD_MISMATCH);
        $e = new Entry($this->entry, $this->log, $this->wrongUser);

        $this->expectException(ObjectException::class);
        $this->expectExceptionCode(ObjectException::NESTED_RECORD_MISMATCH);
        $e = new Entry($this->entry, $this->wrongLog, $this->user);

        $this->expectException(ObjectException::class);
        $this->expectExceptionCode(ObjectException::NESTED_RECORD_MISMATCH);
        $e = new Entry($this->entry, $this->wrongLog, $this->wrongUser);

        $e = new Entry($this->entry, "foo bar");
        $this->assertObjectNotHasAttribute('log', $e);

        $e = new Entry($this->entry, Entry::SUPPRESS_LOG, "foo bar");
        $this->assertObjectNotHasAttribute('user', $e);

        $e = new Entry($this->entry, $this->log, "foo bar");
        $this->assertInstanceOf(Log::class, $e->log);
        $this->assertObjectNotHasAttribute('user', $e);

        $e = new Entry($this->entry, "foo bar", $this->user);
        $this->assertObjectNotHasAttribute('log', $e);
        $this->assertInstanceOf(User::class, $e->user);

        $e = new Entry($this->entry, "foo", "bar");
        $this->assertObjectNotHasAttribute('log', $e);
        $this->assertObjectNotHasAttribute('user', $e);
    }
}
