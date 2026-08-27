<?php


declare(strict_types=1);

namespace MyAILib\Configuration;

final class AIConfig
{
    public function __construct(
        private readonly array $values = []
    )
    {
    }

    public function get(
        string $key,
        mixed  $default = null
    ): mixed
    {
        return $this->values[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->values);
    }

    public function all(): array
    {
        return $this->values;
    }
}
