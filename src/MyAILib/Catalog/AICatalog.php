<?php

declare(strict_types=1);

namespace MyAILib\Catalog;

use AllowDynamicProperties;
use MyAILib\Model\AIModel;
use MyAILib\Provider\ProviderCapability;
use MyAILib\Provider\ProviderFactory;
use MyAILib\Provider\ProviderInterface;
use MyAILib\Provider\ProviderRegistry;

#[AllowDynamicProperties]
final class AICatalog
{
    private readonly ProviderFactory $factory;

    public function __construct(
        ProviderRegistry $registry,
        ProviderFactory $factory
    ) {
        $this->registry = $registry;
        $this->factory = $factory;
    }

    public function provider(string $slug): ProviderInterface
    {
        return $this->registry->create($slug);
    }

    /**
     * @return ProviderInterface[]
     */
    public function providers(): array
    {
        $providers = [];

        foreach ($this->registry->all() as $slug => $class) {
            $providers[] = $this->factory->create($slug);
        }

        return $providers;
    }

    /**
     * @return AIModel[]
     */
    public function models(?string $provider = null): array
    {
        if ($provider !== null) {
            return $this->provider($provider)->getModels();
        }

        $models = [];

        foreach ($this->providers() as $providerInstance) {
            foreach ($providerInstance->getModels() as $model) {
                $models[] = $model;
            }
        }

        return $models;
    }

    /**
     * @return array<string, AIModel[]>
     */
    public function modelsByProvider(): array
    {
        $result = [];

        foreach ($this->registry->all() as $slug => $class) {
            $result[$slug] = $this->registry
                ->create($slug)
                ->getModels();
        }

        return $result;
    }

    /**
     * @return ProviderInterface[]
     */
    public function providersSupporting(
        ProviderCapability $capability
    ): array {
        return array_values(
            array_filter(
                $this->providers(),
                static fn (
                    ProviderInterface $provider
                ): bool => $provider->supports($capability)
            )
        );
    }

    public function hasProvider(string $slug): bool
    {
        return $this->registry->has($slug);
    }
}
