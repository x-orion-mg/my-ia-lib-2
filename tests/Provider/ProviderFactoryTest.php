<?php


declare(strict_types=1);

namespace MyAILib\Tests\Provider;

use MyAILib\Config\AIConfig;
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

}
