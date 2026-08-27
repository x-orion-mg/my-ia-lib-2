<?php


declare(strict_types=1);

namespace MyAILib\Message;

final class Message
{
    public function __construct(
        private readonly MessageRole $role,
        private readonly string      $content,
    )
    {
    }

    public function role(): MessageRole
    {
        return $this->role;
    }

    public function content(): string
    {
        return $this->content;
    }

    public function toArray(): array
    {
        return [
            'role' => $this->role->value,
            'content' => $this->content,
        ];
    }
}
