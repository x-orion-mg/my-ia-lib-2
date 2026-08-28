<?php


declare(strict_types=1);

namespace MyAILib\Agent;

final class ExampleAgent extends AbstractAgent
{
    public function name(): string
    {
        return 'example-agent';
    }

    public function instructions(): string
    {
        return 'Tu es un assistant français. Réponds clairement et simplement.';
    }

    public function run(string $input): mixed
    {
        return $this->ask(
            $input
        );
    }
}
