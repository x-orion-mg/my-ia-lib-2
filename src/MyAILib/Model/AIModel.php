<?php


declare(strict_types=1);

namespace MyAILib\Model;

final class AIModel
{
    public function __construct(
        private readonly string  $id,
        private readonly ?string $name = null,
        private readonly array   $capabilities = [],
    )
    {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name ?? $this->id;
    }

    public function capabilities(): array
    {
        return $this->capabilities;
    }

    public function supports(string $capability): bool
    {
        return in_array(
            $capability,
            $this->capabilities,
            true
        );
    }
}
