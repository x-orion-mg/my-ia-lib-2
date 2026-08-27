<?php

declare(strict_types=1);

namespace MyAILib\Request;

use MyAILib\Message\Message;
use MyAILib\Message\MessageRole;
use MyAILib\Options\GenerationOptions;

final class AIRequest
{
    /**
     * @param Message[] $messages
     */
    public function __construct(
        private readonly array $messages = [],
        private readonly ?GenerationOptions $options = null,
    ) {
    }

    public static function fromPrompt(
        string $prompt,
        ?GenerationOptions $options = null
    ): self {
        return new self(
            messages: [
                new Message(
                    MessageRole::USER,
                    $prompt
                ),
            ],
            options: $options
        );
    }

    /**
     * @return Message[]
     */
    public function messages(): array
    {
        return $this->messages;
    }

    public function options(): ?GenerationOptions
    {
        return $this->options;
    }

    public function getPrompt(): string
    {
        foreach (array_reverse($this->messages) as $message) {
            if ($message->role() === MessageRole::USER) {
                return $message->content();
            }
        }

        return '';
    }

    public function toArray(): array
    {
        return array_map(
            static fn (Message $message) => $message->toArray(),
            $this->messages
        );
    }
}
