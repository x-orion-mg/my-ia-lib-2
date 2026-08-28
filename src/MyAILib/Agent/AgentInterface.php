<?php

declare(strict_types=1);

namespace MyAILib\Agent;

interface AgentInterface
{
    public function name(): string;
    public function instructions(): string;
    public function run(string $input): mixed;
}