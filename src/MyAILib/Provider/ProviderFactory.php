<?php

declare(strict_types=1);

namespace MyAILib\Provider;

use InvalidArgumentException;

final class ProviderFactory
{
    /**
     * @param array<string, callable(): ProviderInterface> $factories
     */
    public function __construct(
        private readonly ProviderRegistry $registry,
        private readonly array $factories = []
    ) {
    }

    public function create(
        string $slug,
        array $options = []
    ): ProviderInterface {
        if (!$this->registry->has($slug)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Provider "%s" is not registered.',
                    $slug
                )
            );
        }

        if (isset($this->factories[$slug])) {
            $provider = ($this->factories[$slug])();
        } else {
            $provider = $this->registry->create($slug);
        }

        if ($options !== []) {
            $provider->configure($options);
        }

        return $provider;
    }
}
