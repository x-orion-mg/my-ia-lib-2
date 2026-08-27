<?php


declare(strict_types=1);

namespace MyAILib\Options;

use InvalidArgumentException;

final class GenerationOptions
{
    public function __construct(
        private readonly ?float $temperature = null,
        private readonly ?int   $maxTokens = null,
        private readonly ?float $topP = null,
        private readonly ?float $frequencyPenalty = null,
        private readonly ?float $presencePenalty = null,
    )
    {
        $this->validate();
    }

    public function temperature(): ?float
    {
        return $this->temperature;
    }

    public function maxTokens(): ?int
    {
        return $this->maxTokens;
    }

    public function topP(): ?float
    {
        return $this->topP;
    }

    public function frequencyPenalty(): ?float
    {
        return $this->frequencyPenalty;
    }

    public function presencePenalty(): ?float
    {
        return $this->presencePenalty;
    }

    public function toArray(): array
    {
        return array_filter([
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
            'top_p' => $this->topP,
            'frequency_penalty' => $this->frequencyPenalty,
            'presence_penalty' => $this->presencePenalty,
        ], static fn($value) => $value !== null);
    }

    private function validate(): void
    {
        if (
            $this->temperature !== null &&
            ($this->temperature < 0 || $this->temperature > 2)
        ) {
            throw new InvalidArgumentException(
                'Temperature must be between 0 and 2.'
            );
        }

        if (
            $this->maxTokens !== null &&
            $this->maxTokens <= 0
        ) {
            throw new InvalidArgumentException(
                'maxTokens must be greater than 0.'
            );
        }

        if (
            $this->topP !== null &&
            ($this->topP < 0 || $this->topP > 1)
        ) {
            throw new InvalidArgumentException(
                'topP must be between 0 and 1.'
            );
        }
    }
}
