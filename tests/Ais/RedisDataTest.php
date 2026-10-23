<?php

namespace Ais\tests;

use Ais\Message\Message123;
use Ais\Request\Config;
use Ais\Request\RedisData;

use PHPUnit\Framework\TestCase;

/**
 * Integrationstest, der einen Redis-Server benötigt. Ohne Redis wird er übersprungen.
 * Standard ist 127.0.0.1:6379, über REDIS_HOST (Hostname oder Socket-Pfad) und REDIS_PORT änderbar.
 */
class RedisDataTest extends TestCase
{
    private const TEST_PORT = 'phpunit';

    private RedisData $redisData;

    protected function setUp(): void
    {
        $this->redisData = $this->createRedisData(getenv('REDIS_HOST') ?: '127.0.0.1', getenv('REDIS_PORT') ?: 6379);

        try {
            $this->redisData->connect();
        } catch (\Exception $e) {
            $this->markTestSkipped('Kein Redis-Server erreichbar: ' . $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        $this->redisData->replace([]);
        $this->redisData->close();
    }

    public function testReplaceOverwritesExistingData()
    {
        $this->redisData->replace([$this->createMessage(211000001), $this->createMessage(211000002)]);
        $this->redisData->replace([$this->createMessage(211000003)]);

        $result = $this->redisData->read(true);

        $this->assertCount(1, $result);
        $this->assertSame(211000003, $result[0]->mmsi);
    }

    public function testIsUnixSocket()
    {
        $this->assertTrue($this->createRedisData('/var/run/redis/redis.sock')->isUnixSocket());
        $this->assertFalse($this->createRedisData('127.0.0.1')->isUnixSocket());
        $this->assertFalse($this->createRedisData('redis.example.org')->isUnixSocket());
    }

    public function testReplaceWithEmptyArrayRemovesData()
    {
        $this->redisData->replace([$this->createMessage(211000001)]);
        $this->redisData->replace([]);

        $this->assertSame([], $this->redisData->read(true));
    }

    private function createRedisData($redisIp, $redisPort = 6379)
    {
        $configFile = tempnam(sys_get_temp_dir(), 'ais');
        file_put_contents($configFile, json_encode(['redis_ip' => $redisIp, 'redis_port' => $redisPort]));
        $redisData = new RedisData(new Config($configFile), self::TEST_PORT);
        unlink($configFile);

        return $redisData;
    }

    private function createMessage($mmsi)
    {
        $message = new Message123(1, 1700000000);
        $message->mmsi = $mmsi;
        $message->longitude = 9.145;
        $message->latitude = 53.891;

        return $message;
    }

}
