<?php


declare(strict_types=1);

namespace MyAILib\Http;

final class HttpResponse
{
    public function __construct(
        private readonly int    $statusCode,
        private readonly string $body,
        private readonly array  $headers = [],
    )
    {
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200
            && $this->statusCode < 300;
    }
}
