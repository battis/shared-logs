<?php

namespace Battis\SharedLogs\Tests\Objects;

use Battis\SharedLogs\AbstractObject;
use Battis\SharedLogs\Objects\User;
use Battis\SharedLogs\Tests\AbstractObjectTest;

class UserTest extends AbstractObjectTest
{
    protected $user;

    protected function setUp()
    {
        parent::setUp();
        $this->user = self::$records['users'][0];
    }

    public function testInstantiation()
    {
        $u = new User($this->user);
        $this->assertInstanceOf(User::class, $u);
        $this->reconcileFields($this->user, $u);

        return $u;
    }

    /**
     * @depends testInstantiation
     */
    public function testJsonSerialization(AbstractObject $u)
    {
        $json = json_encode($u);
        $record = $this->user;
        unset($record['password']);
        $this->assertJson($json);
        $this->assertJsonStringEqualsJsonString(json_encode($record), $json);
    }
}
