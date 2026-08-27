<?php

declare(strict_types=1);

namespace MyAILib\Session;

use DateTimeImmutable;
use MyAILib\Message\MessageRole;
use RuntimeException;
use MyAILib\Message\Message;

final class FileSessionStore implements SessionStoreInterface
{
    public function __construct(
        private readonly string $directory
    ) {
        if (!is_dir($this->directory)) {
            if (!mkdir($this->directory, 0775, true) && !is_dir($this->directory)) {
                throw new RuntimeException(
                    sprintf(
                        'Unable to create session directory "%s".',
                        $this->directory
                    )
                );
            }
        }
    }

    public function get(string $id): ?AISession
    {
        $file = $this->filePath($id);

        if (!is_file($file)) {
            return null;
        }

        $json = file_get_contents($file);

        if ($json === false) {
            throw new RuntimeException(
                sprintf(
                    'Unable to read session file "%s".',
                    $file
                )
            );
        }

        $data = json_decode($json, true);

        if (!is_array($data)) {
            throw new RuntimeException(
                sprintf(
                    'Invalid session data in "%s".',
                    $file
                )
            );
        }

        return $this->hydrate($data);
    }

    public function save(SessionInterface $session): void
    {
        $file = $this->filePath($session->id());

        $data = [
            'id' => $session->id(),
            'title' => $session->title(),
            'system_prompt' => $session->getSystemPrompt(),
            'created_at' => $session->createdAt()->format(DATE_ATOM),
            'updated_at' => $session->updatedAt()->format(DATE_ATOM),
            'messages' => array_map(
                static function ($message): array {
                    return [
                        'role' => $message->role()->value,
                        'content' => $message->content(),
                    ];
                },
                $session->messages()
            ),
        ];

        $json = json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );

        $temporaryFile = $file . '.tmp';

        if (file_put_contents($temporaryFile, $json, LOCK_EX) === false) {
            throw new RuntimeException(
                sprintf(
                    'Unable to write session file "%s".',
                    $temporaryFile
                )
            );
        }

        if (!rename($temporaryFile, $file)) {
            @unlink($temporaryFile);

            throw new RuntimeException(
                sprintf(
                    'Unable to persist session file "%s".',
                    $file
                )
            );
        }
    }

    public function delete(string $id): void
    {
        $file = $this->filePath($id);

        if (is_file($file) && !unlink($file)) {
            throw new RuntimeException(
                sprintf(
                    'Unable to delete session file "%s".',
                    $file
                )
            );
        }
    }

    /**
     * @return AISession[]
     */
    public function all(): array
    {
        $files = glob(
            $this->directory . DIRECTORY_SEPARATOR . '*.json'
        );

        if ($files === false) {
            return [];
        }

        $sessions = [];

        foreach ($files as $file) {
            $json = file_get_contents($file);

            if ($json === false) {
                continue;
            }

            $data = json_decode($json, true);

            if (!is_array($data)) {
                continue;
            }

            $session = $this->hydrate($data);

            $sessions[] = $session;
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

    /**
     * @throws \DateMalformedStringException
     */
    private function hydrate(array $data): AISession
    {
        if (!isset($data['id']) || !is_string($data['id'])) {
            throw new RuntimeException(
                'Session ID is missing or invalid.'
            );
        }

        $session = new AISession($data['id']);

        if (
            isset($data['title'])
            && is_string($data['title'])
            && $data['title'] !== ''
        ) {
            $session->setTitle($data['title']);
        }

        if (
            isset($data['system_prompt'])
            && is_string($data['system_prompt'])
        ) {
            $session->setSystemPrompt($data['system_prompt']);
        }

        if (isset($data['messages']) && is_array($data['messages'])) {
            foreach ($data['messages'] as $message) {
                if (
                    !is_array($message)
                    || !isset($message['role'], $message['content'])
                    || !is_string($message['role'])
                    || !is_string($message['content'])
                ) {
                    continue;
                }

                $session->addMessage(
                    new Message(
                        MessageRole::from($message['role']),
                        $message['content']
                    )
                );
            }
        }

        if (
            isset($data['created_at'], $data['updated_at'])
            && is_string($data['created_at'])
            && is_string($data['updated_at'])
        ) {
            $session->restoreTimestamps(
                new DateTimeImmutable($data['created_at']),
                new DateTimeImmutable($data['updated_at'])
            );
        }

        return $session;
    }

    private function filePath(string $id): string
    {
        /*
         * On ne met jamais directement l'ID dans le nom du fichier.
         * Cela évite les problèmes de traversal avec ../ ou caractères spéciaux.
         */
        $safeId = hash('sha256', $id);

        return $this->directory
            . DIRECTORY_SEPARATOR
            . $safeId
            . '.json';
    }
}
