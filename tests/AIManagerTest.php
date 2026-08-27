<?php


declare(strict_types=1);

namespace tests;

use MyAILib\AI\AIManager;
use MyAILib\Config\ConfigLoader;
use MyAILib\Provider\ProviderFactory;
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
        $factory = new ProviderFactory($registry);
        $ai = AIManager::create(
            'fake',
            $factory,
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

        $factory = new ProviderFactory($registry);

        $ai = AIManager::create(
            'fake',
            $factory
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

    public function testFactoryCreatesRegisteredProvider(): void
    {
        $registry = new ProviderRegistry();

        $registry->register(
            'fake',
            FakeProvider::class
        );

        $factory = new ProviderFactory($registry);

        $provider = $factory->create(
            'fake',
            [
                'model' => 'factory-model',
            ]
        );

        $this->assertInstanceOf(
            FakeProvider::class,
            $provider
        );
    }
    public function testCreateUsesDefaultProviderWhenSlugIsNull(): void
    {
        $registry = new ProviderRegistry();

        $registry->register(
            'fake',
            FakeProvider::class
        );

        $config = ConfigLoader::fromArray([
            'default_provider' => 'fake',
            'providers' => [
                'fake' => [],
            ],
        ]);

        $factory = new ProviderFactory(
            $registry,
            $config
        );

        $ai = AIManager::create(
            null,
            $factory
        );

        self::assertInstanceOf(
            FakeProvider::class,
            $ai->getProvider()
        );
    }

}
