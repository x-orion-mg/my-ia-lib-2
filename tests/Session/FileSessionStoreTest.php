<?php

declare(strict_types=1);

namespace MyAILib\Tests\Session;

use MyAILib\Message\Message;
use MyAILib\Message\MessageRole;
use MyAILib\Session\AISession;
use MyAILib\Session\FileSessionStore;
use PHPUnit\Framework\TestCase;

final class FileSessionStoreTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . 'my-ai-lib-' . uniqid('', true);
    }

    protected function tearDown(): void
    {
        if (!is_dir($this->directory)) {
            return;
        }

        $files = glob($this->directory . DIRECTORY_SEPARATOR . '*');

        if ($files !== false) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }

        @rmdir($this->directory);
    }

    public function testSessionCanBeSavedAndRetrieved(): void
    {
        $store = new FileSessionStore($this->directory);

        $session = new AISession('conversation-1');

        $session->setTitle('Discussion PHP');

        $session->addMessage(
            new Message(
                MessageRole::USER,
                'Bonjour'
            )
        );

        $session->addMessage(
            new Message(
                MessageRole::ASSISTANT,
                'Bonjour, comment puis-je vous aider ?'
            )
        );

        $store->save($session);

        $restored = $store->get('conversation-1');

        self::assertNotNull($restored);
        self::assertSame(
            'conversation-1',
            $restored->id()
        );
        self::assertSame(
            'Discussion PHP',
            $restored->title()
        );
        self::assertCount(
            2,
            $restored->messages()
        );
        self::assertSame(
            'Bonjour',
            $restored->messages()[0]->content()
        );
    }

    public function testSessionsCanBeListed(): void
    {
        $store = new FileSessionStore($this->directory);

        $first = new AISession('conversation-1');
        $first->setTitle('PHP');

        $second = new AISession('conversation-2');
        $second->setTitle('Recettes');

        $store->save($first);
        $store->save($second);

        $sessions = $store->all();

        self::assertCount(2, $sessions);
    }

    public function testSessionCanBeDeleted(): void
    {
        $store = new FileSessionStore($this->directory);

        $session = new AISession('conversation-1');

        $store->save($session);

        self::assertNotNull(
            $store->get('conversation-1')
        );

        $store->delete('conversation-1');

        self::assertNull(
            $store->get('conversation-1')
        );
    }
}
