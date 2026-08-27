<?php


declare(strict_types=1);

namespace MyAILib\Config;

final class AIConfig
{
    public function __construct(
        private readonly array $providers = []
    )
    {
    }

    public function provider(string $slug): array
    {
        return $this->providers[$slug] ?? [];
    }

    public function hasProvider(string $slug): bool
    {
        return isset($this->providers[$slug]);
    }

    public function all(): array
    {
        return $this->providers;
    }
}
