<?php


declare(strict_types=1);

namespace MyAILib\Tests\Catalog;

use MyAILib\Catalog\AICatalog;
use MyAILib\Provider\ProviderCapability;
use MyAILib\Provider\ProviderRegistry;
use MyAILib\Tests\FakeProvider;
use PHPUnit\Framework\TestCase;
use MyAILib\Provider\ProviderFactory;

final class AICatalogTest extends TestCase
{
    private function createCatalog(): AICatalog
    {
        $registry = new ProviderRegistry();

        $registry->register(
            'fake',
            FakeProvider::class
        );

        $factory = new ProviderFactory($registry);

        return new AICatalog(
            $registry,
            $factory
        );
    }


    public function testCanRetrieveProvider(): void
    {
        $catalog = $this->createCatalog();

        $provider = $catalog->provider('fake');

        $this->assertSame(
            'fake',
            $provider->getSlug()
        );
    }

    public function testCanRetrieveModels(): void
    {
        $catalog = $this->createCatalog();

        $models = $catalog->models('fake');

        $this->assertNotEmpty($models);

        $this->assertSame(
            'fake-model',
            $models[0]->id()
        );
    }

    public function testCanFindProvidersByCapability(): void
    {
        $catalog = $this->createCatalog();

        $providers = $catalog->providersSupporting(
            ProviderCapability::CHAT
        );

        $this->assertCount(
            1,
            $providers
        );

        $this->assertSame(
            'fake',
            $providers[0]->getSlug()
        );
    }
}
