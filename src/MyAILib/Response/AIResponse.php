<?php

declare(strict_types=1);

namespace MyAILib\Response;

final class AIResponse
{
    public function __construct(
        private readonly string $text,
        private readonly ?string $provider = null,
        private readonly ?string $model = null,
        private readonly array $metadata = [],
    ) {
    }

    public function text(): string
    {
        return $this->text;
    }

    public function provider(): ?string
    {
        return $this->provider;
    }

    public function model(): ?string
    {
        return $this->model;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }
}
