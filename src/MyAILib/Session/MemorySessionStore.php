<?php

declare(strict_types=1);

namespace MyAILib\Session;

final class MemorySessionStore implements SessionStoreInterface
{
    /**
     * @var array<string, SessionInterface>
     */
    private array $sessions = [];

    public function get(string $id): ?SessionInterface
    {
        return $this->sessions[$id] ?? null;
    }

    public function save(SessionInterface $session): void
    {
        $this->sessions[$session->id()] = $session;
    }

    public function delete(string $id): void
    {
        unset($this->sessions[$id]);
    }

    /**
     * @return SessionInterface[]
     */
    public function all(): array
    {
        return array_values($this->sessions);
    }

}
