<?php


declare(strict_types=1);

namespace MyAILib\Tests\Provider;

use MyAILib\AI\AIManager;
use MyAILib\Config\AIConfig;
use MyAILib\Config\ConfigLoader;
use MyAILib\Provider\ProviderFactory;
use MyAILib\Provider\ProviderRegistry;
use MyAILib\Tests\FakeProvider;
use PHPUnit\Framework\TestCase;

final class ProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesRegisteredProvider(): void
    {
        $registry = new ProviderRegistry();

        $registry->register(
            'fake',
            FakeProvider::class
        );

        $factory = new ProviderFactory($registry);

        $provider = $factory->create('fake');

        $this->assertInstanceOf(
            FakeProvider::class,
            $provider
        );
    }

    public function testFactoryCanUseCustomFactory(): void
    {
        $registry = new ProviderRegistry();

        $registry->register(
            'fake',
            FakeProvider::class
        );

        $factory = new ProviderFactory(
            $registry,
            null,
            [
                'fake' => fn () => new FakeProvider(),
            ]
        );
        $provider = $factory->create('fake');

        $this->assertInstanceOf(
            FakeProvider::class,
            $provider
        );
    }

    public function testFactoryUsesConfiguration(): void
    {
        $registry = new ProviderRegistry();

        $registry->register(
            'fake',
            FakeProvider::class
        );

        $config = new AIConfig([
            'fake' => [
                'model' => 'configured-model',
            ],
        ]);

        $factory = new ProviderFactory(
            $registry,
            $config
        );

        $provider = $factory->create('fake');

        $this->assertSame(
            'configured-model',
            $provider->getConfiguredOption('model')
        );
    }
    public function testFactoryResolvesDefaultProvider(): void
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

        $this->assertSame(
            'fake',
            $factory->resolve(null)
        );
    }

    public function testExplicitProviderHasPriorityOverDefault(): void
    {
        $registry = new ProviderRegistry();

        $registry->register(
            'fake',
            FakeProvider::class
        );

        $registry->register(
            'other',
            FakeProvider::class
        );

        $config = ConfigLoader::fromArray([
            'default_provider' => 'fake',
            'providers' => [
                'fake' => [],
                'other' => [],
            ],
        ]);

        $factory = new ProviderFactory(
            $registry,
            $config
        );

        $this->assertSame(
            'other',
            $factory->resolve('other')
        );
    }
    public function testExplicitOptionsOverrideProviderConfiguration(): void
    {
        $registry = new ProviderRegistry();

        $registry->register(
            'fake',
            FakeProvider::class
        );

        $config = ConfigLoader::fromArray([
            'providers' => [
                'fake' => [
                    'model' => 'default-model',
                    'temperature' => 0.5,
                ],
            ],
        ]);

        $factory = new ProviderFactory(
            $registry,
            $config
        );

        $provider = $factory->create(
            'fake',
            [
                'model' => 'custom-model',
            ]
        );

        $this->assertSame(
            'custom-model',
            $provider->getConfiguredOption('model')
        );

        $this->assertSame(
            0.5,
            $provider->getConfiguredOption('temperature')
        );
    }

    public function testFactoryMergesProviderConfigurationWithExplicitOptions(): void
    {
        $registry = new ProviderRegistry();

        $registry->register(
            'fake',
            FakeProvider::class
        );

        $config = ConfigLoader::fromArray([
            'providers' => [
                'fake' => [
                    'model' => 'default-model',
                    'temperature' => 0.5,
                ],
            ],
        ]);

        $factory = new ProviderFactory(
            $registry,
            $config
        );

        $provider = $factory->create(
            'fake',
            [
                'model' => 'custom-model',
            ]
        );

        self::assertInstanceOf(
            FakeProvider::class,
            $provider
        );

        self::assertSame(
            'custom-model',
            $provider->options()['model']
        );

        self::assertSame(
            0.5,
            $provider->options()['temperature']
        );
    }

    public function testAIManagerCanSelectModelExplicitly(): void
    {
        $registry = new ProviderRegistry();

        $registry->register(
            'fake',
            FakeProvider::class
        );

        $config = ConfigLoader::fromArray([
            'default_provider' => 'fake',
            'providers' => [
                'fake' => [
                    'model' => 'default-model',
                ],
            ],
        ]);

        $factory = new ProviderFactory(
            $registry,
            $config
        );

        $ai = AIManager::create(
            null,
            $factory,
            [
                'model' => 'custom-model',
            ]
        );

        $provider = $ai->getProvider();

        self::assertInstanceOf(
            FakeProvider::class,
            $provider
        );

        self::assertSame(
            'custom-model',
            $provider->options()['model']
        );
    }

}
