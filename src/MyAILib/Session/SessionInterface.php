<?php


declare(strict_types=1);

namespace MyAILib\Session;

use MyAILib\Message\Message;

interface SessionInterface
{
    public function id(): string;

    public function addMessage(Message $message): void;

    /**
     * @return Message[]
     */
    public function messages(): array;

    public function clear(): void;

    public function setSystemPrompt(string $prompt): void;

    public function getSystemPrompt(): ?string;

}
