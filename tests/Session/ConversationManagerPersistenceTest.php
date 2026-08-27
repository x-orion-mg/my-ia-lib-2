<?php

declare(strict_types=1);

namespace MyAILib\Tests\Session;

use MyAILib\Message\Message;
use MyAILib\Message\MessageRole;
use MyAILib\Session\ConversationManager;
use MyAILib\Session\FileSessionStore;
use PHPUnit\Framework\TestCase;

final class ConversationManagerPersistenceTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory =
            sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . 'my-ai-lib-conversations-' . uniqid('', true);
    }

    protected function tearDown(): void
    {
        if (!is_dir($this->directory)) {
            return;
        }

        $files = glob(
            $this->directory . DIRECTORY_SEPARATOR . '*'
        );

        if ($files !== false) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }

        @rmdir($this->directory);
    }

    /**
     * @throws \JsonException
     */
    public function testConversationSurvivesNewManagerInstance(): void
    {
        $store = new FileSessionStore($this->directory);

        $manager = new ConversationManager($store);

        $conversation = $manager->create('conversation-1');

        $conversation->setTitle('Discussion PHP');

        $conversation->addMessage(
            new Message(
                MessageRole::USER,
                'Bonjour'
            )
        );

        $conversation->addMessage(
            new Message(
                MessageRole::ASSISTANT,
                'Bonjour, comment puis-je vous aider ?'
            )
        );

        $store->save($conversation);

        /*
         * Nouvelle instance du store.
         * On simule ici un nouveau chargement de l'application.
         */
        $newStore = new FileSessionStore($this->directory);

        $newManager = new ConversationManager($newStore);

        $restored = $newManager->get('conversation-1');

       $this->assertNotNull($restored);

        $this->assertSame(
            'conversation-1',
            $restored->id()
        );

        $this->assertSame(
            'Discussion PHP',
            $restored->title()
        );

        $this->assertCount(
            2,
            $restored->messages()
        );

        $this->assertSame(
            'Bonjour',
            $restored->messages()[0]->content()
        );

        $this->assertSame(
            'Bonjour, comment puis-je vous aider ?',
            $restored->messages()[1]->content()
        );
    }

    /**
     * @throws \JsonException
     */
    public function testCanListPersistedConversations(): void
    {
        $store = new FileSessionStore($this->directory);

        $manager = new ConversationManager($store);

        $first = $manager->create('conversation-1');
        $first->setTitle('Projet IA');
        $store->save($first);

        $second = $manager->create('conversation-2');
        $second->setTitle('Recette');
        $store->save($second);

        /*
         * Nouveau manager.
         */
        $newStore = new FileSessionStore($this->directory);
        $newManager = new ConversationManager($newStore);

        $conversations = $newManager->all();

        $this->assertCount(
            2,
            $conversations
        );

        $ids = array_map(
            static fn ($conversation): string => $conversation->id(),
            $conversations
        );

        $this->assertContains(
            'conversation-1',
            $ids
        );

        $this->assertContains(
            'conversation-2',
            $ids
        );
    }

    public function testCanDeletePersistedConversation(): void
    {
        $store = new FileSessionStore($this->directory);

        $manager = new ConversationManager($store);

        $manager->create('conversation-1');

        $this->assertNotNull(
            $manager->get('conversation-1')
        );

        $manager->delete('conversation-1');

        /*
         * Vérification avec un nouveau store.
         */
        $newStore = new FileSessionStore($this->directory);

        $this->assertNull(
            $newStore->get('conversation-1')
        );
    }
}
