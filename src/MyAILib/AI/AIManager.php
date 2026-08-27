<?php

declare(strict_types=1);

namespace MyAILib\AI;

use MyAILib\Provider\ProviderFactory;
use MyAILib\Provider\ProviderInterface;
use MyAILib\Request\AIRequest;
use MyAILib\Response\AIResponse;

final readonly class AIManager implements AIInterface
{
    public function __construct(
        private ProviderInterface $provider
    ) {
    }

    public static function create(
        string $providerSlug,
        ProviderFactory $factory,
        array $options = []
    ): self {
        return new self(
            $factory->create(
                $providerSlug,
                $options
            )
        );
    }

    public function ask( string|AIRequest $request ): AIResponse {
        if (is_string($request)) {
            $request = new AIRequest($request);
        }

        return $this->provider->ask($request);
    }

    public function getProvider(): ProviderInterface
    {
        return $this->provider;
    }
}
