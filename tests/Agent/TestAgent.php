<?php

declare(strict_types=1);

namespace MyAILib\Tests\Agent;

use MyAILib\Agent\AbstractAgent;

final class TestAgent extends AbstractAgent
{
    public function name(): string
    {
        return 'test-agent';
    }

    public function run(string $input): mixed
    {
        return $this->ask(
            'Réponds simplement à cette demande : ' . $input
        );
    }
}