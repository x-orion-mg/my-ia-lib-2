<?php

declare(strict_types=1);

namespace MyAILib\Provider;

use InvalidArgumentException;

final class ProviderRegistry
{
    /**
     * @var array<string, class-string<ProviderInterface>>
     */
    private array $providers = [];

    /**
     * @param class-string<ProviderInterface> $providerClass
     */
    public function register(string $slug, string $providerClass): void
    {
        if (!is_a($providerClass, ProviderInterface::class, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Provider "%s" must implement %s.',
                    $providerClass,
                    ProviderInterface::class
                )
            );
        }

        $this->providers[$slug] = $providerClass;
    }

    public function has(string $slug): bool
    {
        return isset($this->providers[$slug]);
    }

    /**
     * @return class-string<ProviderInterface>
     */
    public function get(string $slug): string
    {
        if (!$this->has($slug)) {
            throw new InvalidArgumentException(
                sprintf('Provider "%s" is not registered.', $slug)
            );
        }

        return $this->providers[$slug];
    }

    /**
     * @return array<string, class-string<ProviderInterface>>
     */
    public function all(): array
    {
        return $this->providers;
    }

    public function create(
        string $slug,
        array $options = []
    ): ProviderInterface {
        $providerClass = $this->get($slug);

        $provider = new $providerClass();

        if ($options !== []) {
            $provider->configure($options);
        }

        return $provider;
    }
}
