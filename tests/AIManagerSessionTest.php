<?php


declare(strict_types=1);

namespace MyAILib\Tests;

use JetBrains\PhpStorm\NoReturn;
use MyAILib\AI\AIManager;
use MyAILib\Provider\ProviderRegistry;
use MyAILib\Provider\ProviderFactory;
use MyAILib\Session\MemorySessionStore;
use PHPUnit\Framework\TestCase;

final class AIManagerSessionTest extends TestCase
{
    #[NoReturn]
    public function testAskUsesSessionContext(): void
    {
        $registry = new ProviderRegistry();

        $registry->register(
            'fake-2',
            FakeProvider::class
        );

        $factory = new ProviderFactory($registry);

        $store = new MemorySessionStore();

        $ai = AIManager::create(
            'fake-2',
            $factory,
            [],
            $store
        );

        $ai->startSession('conversation-1');

        $ai->ask('Bonjour');

        $ai->ask('Comment vas-tu ?');

        $session = $ai->getSession();

        $this->assertNotNull($session);

        $this->assertCount(
            4,
            $session->messages()
        );

        $this->assertSame(
            'Bonjour',
            $session->messages()[0]->content()
        );

        $this->assertSame(
            'Comment vas-tu ?',
            $session->messages()[2]->content()
        );
    }
}
