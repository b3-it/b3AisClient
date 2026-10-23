<?php

namespace Ais\tests;

use Ais\Message\Message123;
use Ais\Request\Config;
use Ais\Request\RedisData;

use PHPUnit\Framework\TestCase;

/**
 * Integrationstest, der einen Redis-Server unter 127.0.0.1:6379 benötigt. Ohne Redis wird er übersprungen.
 */
class RedisDataTest extends TestCase
{
    private const TEST_PORT = 'phpunit';

    private RedisData $redisData;

    protected function setUp(): void
    {
        $configFile = tempnam(sys_get_temp_dir(), 'ais');
        file_put_contents($configFile, json_encode(['redis_ip' => '127.0.0.1', 'redis_port' => 6379]));
        $config = new Config($configFile);
        unlink($configFile);

        $this->redisData = new RedisData($config, self::TEST_PORT);

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

    public function testReplaceWithEmptyArrayRemovesData()
    {
        $this->redisData->replace([$this->createMessage(211000001)]);
        $this->redisData->replace([]);

        $this->assertSame([], $this->redisData->read(true));
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
