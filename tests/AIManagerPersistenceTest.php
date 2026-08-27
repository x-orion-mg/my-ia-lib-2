<?php

declare(strict_types=1);

namespace MyAILib\Tests;

use MyAILib\AI\AIManager;
use MyAILib\Http\HttpResponse;
use MyAILib\Provider\OpenRouter\OpenRouterProvider;
use MyAILib\Provider\ProviderFactory;
use MyAILib\Provider\ProviderRegistry;
use MyAILib\Request\AIRequest;
use MyAILib\Session\FileSessionStore;
use PHPUnit\Framework\TestCase;
use MyAILib\Tests\Http\FakeHttpClient;

final class AIManagerPersistenceTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory =
            sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . 'my-ai-lib-persistence-' . uniqid('', true);
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

    public function testConversationSurvivesNewAIManagerInstance(): void
    {
        /*
         * Premier AIManager
         */

        $http = new FakeHttpClient([
            new HttpResponse(
                200,
                json_encode([
                    'model' => 'test-model',
                    'choices' => [
                        [
                            'message' => [
                                'content' => 'Bonjour, je vais bien.',
                            ],
                        ],
                    ],
                ], JSON_THROW_ON_ERROR)
            ),
        ]);

        $registry = new ProviderRegistry();

        $registry->register(
            'openrouter',
            OpenRouterProvider::class
        );

        $factory = new ProviderFactory(
            $registry
        );

        /*
         * Provider de test.
         *
         * On utilise ici directement le provider configuré
         * avec notre FakeHttpClient.
         */
        $provider = new OpenRouterProvider($http);

        $provider->configure([
            'api_key' => 'test-key',
            'model' => 'test-model',
        ]);

        $store = new FileSessionStore($this->directory);

        $ai = new AIManager(
            $provider,
            $store
        );

        $ai->startSession('conversation-1');

        $ai->setSystemPrompt(
            'Tu es un assistant français.'
        );

        $response = $ai->ask(
            AIRequest::fromPrompt('Bonjour')
        );

        self::assertSame(
            'Bonjour, je vais bien.',
            $response->text()
        );

        /*
         * Nouvelle instance de AIManager.
         *
         * Le but est de vérifier que la session
         * ne dépend plus de l'ancienne instance.
         */

        $http2 = new FakeHttpClient([]);

        $provider2 = new OpenRouterProvider($http2);

        $provider2->configure([
            'api_key' => 'test-key',
            'model' => 'test-model',
        ]);

        $ai2 = new AIManager(
            $provider2,
            $store
        );

        $ai2->startSession('conversation-1');

        $session = $ai2->getSession();

        self::assertNotNull($session);

        self::assertSame(
            'conversation-1',
            $session->id()
        );

        self::assertSame(
            'Tu es un assistant français.',
            $session->getSystemPrompt()
        );

        self::assertCount(
            2,
            $session->messages()
        );

        self::assertSame(
            'Bonjour',
            $session->messages()[0]->content()
        );

        self::assertSame(
            'Bonjour, je vais bien.',
            $session->messages()[1]->content()
        );
    }
}
