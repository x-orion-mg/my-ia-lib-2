<?php

declare(strict_types=1);

namespace MyAILib\Config;

final class AIConfig
{
    /**
     * @param array<string, array<string, mixed>> $providers
     */
    public function __construct(
        private readonly array $providers = [],
        private readonly ?string $defaultProvider = null,
    ) {
    }

    public function hasProvider(string $slug): bool
    {
        return isset($this->providers[$slug]);
    }

    /**
     * @return array<string, mixed>
     */
    public function provider(string $slug): array
    {
        return $this->providers[$slug] ?? [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function providers(): array
    {
        return $this->providers;
    }

    public function defaultProvider(): ?string
    {
        return $this->defaultProvider;
    }
}
