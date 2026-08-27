<?php


declare(strict_types=1);

namespace MyAILib\Exception;

class ProviderException extends AIException
{
    public function __construct(
        string                   $message,
        private readonly ?string $provider = null,
        private readonly ?int    $statusCode = null,
        ?\Throwable              $previous = null
    )
    {
        parent::__construct(
            $message,
            $statusCode ?? 0,
            $previous
        );
    }

    public function provider(): ?string
    {
        return $this->provider;
    }

    public function statusCode(): ?int
    {
        return $this->statusCode;
    }
}
