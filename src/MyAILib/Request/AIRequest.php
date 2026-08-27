<?php

declare(strict_types=1);

namespace MyAILib\Request;

final class AIRequest
{
    public function __construct(
        private readonly string $prompt,
        private readonly array $options = [],
    ) {
        if (trim($prompt) === '') {
            throw new \InvalidArgumentException('Prompt cannot be empty.');
        }
    }

    public function getPrompt(): string
    {
        return $this->prompt;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
