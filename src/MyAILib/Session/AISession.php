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

    private ?string $title = null;

    private \DateTimeImmutable $createdAt;

    private \DateTimeImmutable $updatedAt;

    public function __construct(
        private readonly string $id
    ) {
        $now = new \DateTimeImmutable();

        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function addMessage(Message $message): void
    {
        $this->messages[] = $message;
        $this->touch();
    }

    /**
     * @return Message[]
     */
    public function messages(): array
    {
        return $this->messages;
    }

    public function clear(): void
    {
        $this->messages = [];
        $this->touch();
    }

    public function setSystemPrompt(string $prompt): void
    {
        $this->systemPrompt = $prompt;
        $this->touch();
    }

    public function getSystemPrompt(): ?string
    {
        return $this->systemPrompt;
    }

    public function setTitle(string $title): void
    {
        $this->title = trim($title);
        $this->touch();
    }

    public function title(): ?string
    {
        return $this->title;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function restoreTimestamps(
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ): void {
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
