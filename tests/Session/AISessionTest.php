<?php

declare(strict_types=1);

namespace MyAILib\Tests\Session;

use MyAILib\Message\Message;
use MyAILib\Message\MessageRole;
use MyAILib\Session\AISession;
use MyAILib\Session\MemorySessionStore;
use PHPUnit\Framework\TestCase;

final class AISessionTest extends TestCase
{
    public function testSessionStoresMessages(): void
    {
        $session = new AISession('test-session');

        $session->addMessage(
            new Message(
                MessageRole::USER,
                'Bonjour'
            )
        );

        $session->addMessage(
            new Message(
                MessageRole::ASSISTANT,
                'Bonjour !'
            )
        );

        $this->assertSame(
            'test-session',
            $session->id()
        );

        $this->assertCount(
            2,
            $session->messages()
        );

        $this->assertSame(
            'Bonjour',
            $session->messages()[0]->content()
        );
    }

    public function testSessionCanBeStored(): void
    {
        $store = new MemorySessionStore();

        $session = new AISession('abc');

        $store->save($session);

        $loaded = $store->get('abc');

        $this->assertSame(
            $session,
            $loaded
        );
    }

    public function testSessionCanBeCleared(): void
    {
        $session = new AISession('test');

        $session->addMessage(
            new Message(
                MessageRole::USER,
                'Bonjour'
            )
        );

        $session->clear();

        $this->assertCount(
            0,
            $session->messages()
        );
    }
}
