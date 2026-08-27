<?php


declare(strict_types=1);

namespace tests;

use MyAILib\AI\AIManager;
use MyAILib\Provider\ProviderRegistry;
use MyAILib\Tests\FakeProvider;
use PHPUnit\Framework\TestCase;

final class AIManagerTest extends TestCase
{
    public function testProviderCanBeConfigured(): void
    {
        $registry = new ProviderRegistry();

        $registry->register(
            'fake',
            FakeProvider::class
        );

        $ai = AIManager::create(
            'fake',
            $registry,
            [
                'model' => 'custom-model',
            ]
        );

        $response = $ai->ask('Bonjour');

        $this->assertSame(
            'custom-model',
            $response->model()
        );
    }

    public function testAskUsesRegisteredProvider(): void
    {
        $registry = new ProviderRegistry();

        $registry->register(
            'fake',
            FakeProvider::class
        );

        $ai = AIManager::create(
            'fake',
            $registry
        );

        $response = $ai->ask('Bonjour');

        $this->assertSame(
            'Fake response: Bonjour',
            $response->text()
        );

        $this->assertSame(
            'fake',
            $response->provider()
        );

        $this->assertSame(
            'fake-model',
            $response->model()
        );
    }

}
