<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\REST;

use HexagonalPlayground\Infrastructure\API\Bootstrap;
use HexagonalPlayground\Tests\Framework\SlimClient;
use HexagonalPlayground\Tests\Framework\RichClient;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var RichClient */
    protected $client;

    protected function setUp(): void
    {
        $this->client = new RichClient(new SlimClient(Bootstrap::bootstrap()));
    }

    protected static function assertResponseHasValidId($response)
    {
        self::assertObjectHasAttribute('id', $response);
        self::assertGreaterThan(0, strlen($response->id));
    }
}
