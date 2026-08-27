<?php

declare(strict_types=1);

namespace MyAILib\Tests\Session;

use MyAILib\AI\AIManager;
use MyAILib\Session\AISession;
use MyAILib\Session\ConversationManager;
use MyAILib\Session\MemorySessionStore;
use MyAILib\Tests\FakeProvider;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

final class ConversationManagerTest extends TestCase
{
    public function testCanCreateConversation(): void
    {
        $store = new MemorySessionStore();

        $manager = new ConversationManager($store);

        $session = $manager->create('conversation-1');

        $this->assertSame(
            'conversation-1',
            $session->id()
        );
    }

    public function testCanRetrieveConversation(): void
    {
        $store = new MemorySessionStore();

        $manager = new ConversationManager($store);

        $manager->create('conversation-1');

        $session = $manager->get('conversation-1');

        $this->assertNotNull($session);

        $this->assertSame(
            'conversation-1',
            $session->id()
        );
    }

    public function testCanListConversations(): void
    {
        $store = new MemorySessionStore();

        $manager = new ConversationManager($store);

        $manager->create('conversation-1');
        $manager->create('conversation-2');

        $this->assertCount(
            2,
            $manager->all()
        );
    }

    public function testCanRenameConversation(): void
    {
        $store = new MemorySessionStore();

        $manager = new ConversationManager($store);

        $manager->create('conversation-1');

        $manager->rename(
            'conversation-1',
            'Discussion PHP'
        );

        $session = $manager->get('conversation-1');

        $this->assertNotNull($session);

        $this->assertSame(
            'Discussion PHP',
            $session->title()
        );
    }

    public function testCanDeleteConversation(): void
    {
        $store = new MemorySessionStore();

        $manager = new ConversationManager($store);

        $manager->create('conversation-1');

        $manager->delete('conversation-1');

        $this->assertNull(
            $manager->get('conversation-1')
        );
    }

    public function testCanCreateConversationFromMessage(): void
    {
        $store = new MemorySessionStore();

        $manager = new ConversationManager($store);

        $session = $manager->createFromMessage(
            'Comment fonctionne une API REST en PHP ?'
        );

        $this->assertSame(
            'Comment fonctionne une API REST en PHP ?',
            $session->title()
        );

        $this->assertSame(
            $session->id(),
            $manager->get($session->id())?->id()
        );
    }

    public function testConversationTitleIsLimited(): void
    {
        $store = new MemorySessionStore();

        $manager = new ConversationManager($store);

        $message = str_repeat('A', 100);

        $session = $manager->createFromMessage($message);

        $this->assertSame(
            60,
            mb_strlen($session->title())
        );

        $this->assertStringEndsWith(
            '...',
            $session->title()
        );
    }

    /**
     * @throws RandomException
     */
    public function testCanStartAIWithConversation(): void
    {
        $store = new MemorySessionStore();

        $manager = new ConversationManager($store);

        $conversation = $manager->create(
            'conversation-1'
        );

        $ai = new AIManager(
            new FakeProvider(),
            $store
        );

        $started = $manager->start(
            $ai,
            'conversation-1'
        );

        $this->assertSame(
            $conversation->id(),
            $started->id()
        );

        $this->assertNotNull(
            $ai->getSession()
        );

        $this->assertSame(
            'conversation-1',
            $ai->getSession()?->id()
        );
    }

}