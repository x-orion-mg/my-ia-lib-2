<?php

declare(strict_types=1);

namespace MyAILib\Request;

use MyAILib\Message\Message;
use MyAILib\Message\MessageRole;

final readonly class AIRequest
{
    /**
     * @param Message[] $messages
     */
    public function __construct(
        private array $messages = [],
        private array $options = [],
    ) {
    }

    public static function fromPrompt(
        string $prompt,
        array $options = []
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

    public function options(): array
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
