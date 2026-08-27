<?php


declare(strict_types=1);

namespace MyAILib\Session;

use MyAILib\Message\Message;

final class AISession implements SessionInterface
{
    /**
     * @var Message[]
     */
    private array $messages = [];
    private ?string $systemPrompt = null;


    public function __construct(
        private readonly string $id
    )
    {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function addMessage(Message $message): void
    {
        $this->messages[] = $message;
    }

    public function messages(): array
    {
        return $this->messages;
    }

    public function clear(): void
    {
        $this->messages = [];
    }

    public function setSystemPrompt(string $prompt): void
    {
        $this->systemPrompt = $prompt;
    }

    public function getSystemPrompt(): ?string
    {
        return $this->systemPrompt;
    }

}
