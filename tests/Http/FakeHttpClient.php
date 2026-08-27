<?php


declare(strict_types=1);

namespace MyAILib\Tests\Http;

use MyAILib\Http\HttpClientInterface;
use MyAILib\Http\HttpResponse;

final class FakeHttpClient implements HttpClientInterface
{
    public array $requests = [];

    public function __construct(
        private array $responses
    )
    {
    }

    public function post(
        string $url,
        array  $headers = [],
        array  $data = []
    ): HttpResponse
    {
        $this->requests[] = [
            'url' => $url,
            'headers' => $headers,
            'data' => $data,
        ];

        return array_shift($this->responses);
    }
}
