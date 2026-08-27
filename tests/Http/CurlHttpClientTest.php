<?php


declare(strict_types=1);

namespace MyAILib\Tests\Http;

use MyAILib\Http\HttpResponse;
use PHPUnit\Framework\TestCase;

final class CurlHttpClientTest extends TestCase
{
    public function testSuccessfulResponse(): void
    {
        $response = new HttpResponse(
            statusCode: 200,
            body: '{"ok":true}'
        );

        $this->assertTrue(
            $response->isSuccessful()
        );

        $this->assertSame(
            200,
            $response->statusCode()
        );

        $this->assertSame(
            '{"ok":true}',
            $response->body()
        );
    }

    public function testErrorResponse(): void
    {
        $response = new HttpResponse(
            statusCode: 429,
            body: '{"error":"rate limit"}'
        );

        $this->assertFalse(
            $response->isSuccessful()
        );
    }
}
