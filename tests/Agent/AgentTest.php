<?php

declare(strict_types=1);

namespace MyAILib\Tests\Agent;

use MyAILib\AI\AIManager;
use MyAILib\Tests\FakeProvider;
use PHPUnit\Framework\TestCase;

final class AgentTest extends TestCase
{
    public function testAgentUsesAIManager(): void
    {
        $ai = new AIManager(
            new FakeProvider()
        );

        $agent = new TestAgent($ai);

        $this->assertSame(
            'test-agent',
            $agent->name()
        );

        $this->assertSame(
            'Fake response: Réponds simplement à cette demande : Bonjour',
            $agent->run('Bonjour')
        );
    }
}