<?php

declare(strict_types=1);

namespace MyAILib\Tests;

use MyAILib\Provider\ProviderInterface;
use MyAILib\Request\AIRequest;
use MyAILib\Response\AIResponse;

final class FakeProvider implements ProviderInterface
{
    private array $options = [];

    public function configure(array $options): void
    {
        $this->options = $options;
    }

    public function ask(AIRequest $request): AIResponse
    {
        return new AIResponse(
            text: 'Fake response: ' . $request->getPrompt(),
            provider: $this->getSlug(),
            model: $this->options['model'] ?? 'fake-model'
        );
    }

    public function getName(): string
    {
        return 'Fake Provider';
    }

    public function getSlug(): string
    {
        return 'fake';
    }
}
