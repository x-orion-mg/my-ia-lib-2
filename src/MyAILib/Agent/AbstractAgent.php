<?php

declare(strict_types=1);

namespace MyAILib\Agent;

use MyAILib\AI\AIManager;

abstract class AbstractAgent implements AgentInterface
{
    public function __construct(
        protected readonly AIManager $ai
    ) {
    }

    protected function ask(string $prompt): string
    {
        return $this->ai->ask($prompt)->text();
    }
}