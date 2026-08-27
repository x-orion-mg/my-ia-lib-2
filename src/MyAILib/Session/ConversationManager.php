<?php

declare(strict_types=1);

namespace MyAILib\Session;

use MyAILib\AI\AIManager;
use Random\RandomException;

final readonly class ConversationManager
{
    public function __construct(
        private SessionStoreInterface $store
    ) {
    }

    /**
     * @throws RandomException
     */
    public function create(?string $id = null): AISession
    {
        $id ??= bin2hex(random_bytes(16));

        $session = new AISession($id);

        $this->store->save($session);

        return $session;
    }

    public function get(string $id): ?AISession
    {
        $session = $this->store->get($id);

        if ($session === null) {
            return null;
        }

        if (!$session instanceof AISession) {
            return null;
        }

        return $session;
    }

    /**
     * @return AISession[]
     */
    public function all(): array
    {
        $sessions = [];

        foreach ($this->store->all() as $session) {
            if ($session instanceof AISession) {
                $sessions[] = $session;
            }
        }

        usort(
            $sessions,
            static fn (
                AISession $a,
                AISession $b
            ): int => $b->updatedAt() <=> $a->updatedAt()
        );

        return $sessions;
    }

    public function delete(string $id): void
    {
        $this->store->delete($id);
    }

    public function rename(
        string $id,
        string $title
    ): ?AISession {
        $session = $this->get($id);

        if ($session === null) {
            return null;
        }

        $session->setTitle($title);

        $this->store->save($session);

        return $session;
    }

    public function createFromMessage(
        string $message,
        ?string $id = null
    ): AISession {
        $session = $this->create($id);

        $title = trim($message);

        /*
         * Le titre reste volontairement simple.
         * Plus tard, un agent pourra demander à une IA
         * de générer un titre plus intelligent.
         */
        if ($title !== '') {
            if (mb_strlen($title) > 60) {
                $title = mb_substr($title, 0, 57) . '...';
            }

            $session->setTitle($title);

            $this->store->save($session);
        }

        return $session;
    }

    public function start(
        AIManager $ai,
        ?string $id = null
    ): AISession {
        $session = $id !== null
            ? $this->get($id)
            : null;

        if ($session === null) {
            $session = $this->create($id);
        }

        $ai->startSession($session->id());

        return $session;
    }

}