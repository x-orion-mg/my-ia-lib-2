<?php

declare(strict_types=1);

namespace MyAILib\Provider;

use MyAILib\Request\AIRequest;
use MyAILib\Response\AIResponse;

interface ProviderInterface
{
    public function ask(AIRequest $request): AIResponse;

    public function configure(array $options): void;

    public function getName(): string;

    public function getSlug(): string;
}
