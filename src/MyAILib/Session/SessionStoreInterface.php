<?php


declare(strict_types=1);

namespace MyAILib\Session;

interface SessionStoreInterface
{
    public function get(string $id): ?SessionInterface;

    public function save(SessionInterface $session): void;

    public function delete(string $id): void;
}
